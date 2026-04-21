/// <reference types="node" />
import type { IconifyJSON } from '@iconify/types'
import { promises as fs } from 'node:fs'
import { createRequire } from 'node:module'
import { dirname, join, relative } from 'node:path'
import { fileURLToPath } from 'node:url'
import { cleanupSVG, importDirectory, isEmptyColor, parseColors, runSVGO } from '@iconify/tools'
import { getIcons, getIconsCSS, stringToIcon } from '@iconify/utils'

interface BundleScriptCustomSVGConfig {
  dir: string
  monotone: boolean
  prefix: string
}

interface BundleScriptCustomJSONConfig {
  filename: string
  icons?: string[]
}

interface BundleScriptConfig {
  svg?: BundleScriptCustomSVGConfig[]
  icons?: string[]
  json?: (string | BundleScriptCustomJSONConfig)[]
}

const require = createRequire(import.meta.url)
const __dirname = dirname(fileURLToPath(import.meta.url))
const srcDir = join(__dirname, '../../..')
const iconPattern = /\b(?:tabler|bx|mdi|ic)-[a-z0-9-]+\b/gi
const explicitIcons = [
  'tabler-chevron-left',
  'tabler-chevron-right',
]

async function collectUsedIcons (dir: string): Promise<string[]> {
  const entries = await fs.readdir(dir, { withFileTypes: true })
  const icons = new Set<string>(explicitIcons)

  for (const entry of entries) {
    const fullPath = join(dir, entry.name)

    if (entry.isDirectory()) {
      if (entry.name === 'node_modules' || entry.name === 'dist' || entry.name === 'dev-dist') {
        continue
      }

      const nestedIcons = await collectUsedIcons(fullPath)
      nestedIcons.forEach(icon => icons.add(icon))
      continue
    }

    if (!/\.(vue|ts|tsx|js|jsx)$/.test(entry.name)) {
      continue
    }

    if (fullPath === target) {
      continue
    }

    const content = await fs.readFile(fullPath, 'utf8')
    const matches = content.match(iconPattern) ?? []

    matches.forEach(icon => icons.add(icon))
  }

  return Array.from(icons)
}

const baseSources: BundleScriptConfig = {
  svg: [],
  json: [
    {
      filename: require.resolve('@iconify-json/bx/icons.json'),
      icons: [
        'x',
        'error',
        'error-circle',
        'fullscreen',
        'exit-fullscreen',
        'male',
        'female',
      ],
    },
    {
      filename: require.resolve('@iconify-json/mdi/icons.json'),
      icons: [
        'folder-open',
        'folder',
      ],
    },
  ],
}

const target = join(__dirname, 'icons.css')

;(async function () {
  const usedIcons = await collectUsedIcons(srcDir)
  const dir = dirname(target)
  try {
    await fs.mkdir(dir, {
      recursive: true,
    })
  } catch {
    //
  }

  const allIcons: IconifyJSON[] = []
  const sources: BundleScriptConfig = {
    ...baseSources,
    icons: usedIcons,
    json: baseSources.json ? [...baseSources.json] : [],
  }

  if (sources.icons) {
    const sourcesJSON = sources.json ? sources.json : (sources.json = [])

    const organizedList = organizeIconsList(sources.icons)

    for (const prefix in organizedList) {
      const filename = resolveIconJson(prefix)

      sourcesJSON.push({
        filename,
        icons: organizedList[prefix],
      })
    }
  }

  if (sources.json) {
    for (let i = 0; i < sources.json.length; i++) {
      const item = sources.json[i]

      if (!item) {
        continue
      }

      const filename = typeof item === 'string' ? item : item.filename
      const content = JSON.parse(await fs.readFile(filename, 'utf8')) as IconifyJSON

      if (content.prefix === 'tabler' && content.icons) {
        for (const k in content.icons) {
          const icon = content.icons[k]
          if (icon?.body) {
            icon.body = icon.body.replace(/stroke-width="2"/g, 'stroke-width="1.5"')
          }
        }
      }

      if (typeof item !== 'string' && item.icons?.length) {
        const filteredContent = getIcons(content, item.icons)

        if (!filteredContent) {
          throw new Error(`Cannot find required icons in ${filename}`)
        }
        allIcons.push(filteredContent)
      } else {
        allIcons.push(content)
      }
    }
  }

  if (sources.svg) {
    for (let i = 0; i < sources.svg.length; i++) {
      const source = sources.svg[i]

      if (!source) {
        continue
      }

      const iconSet = await importDirectory(source.dir, {
        prefix: source.prefix,
      })

      await iconSet.forEach(async (name, type) => {
        if (type !== 'icon') {
          return
        }

        const svg = iconSet.toSVG(name)

        if (!svg) {
          iconSet.remove(name)

          return
        }

        try {
          await cleanupSVG(svg)

          if (source.monotone) {
            await parseColors(svg, {
              defaultColor: 'currentColor',
              callback: (attr, colorStr, color) => {
                return !color || isEmptyColor(color) ? colorStr : 'currentColor'
              },
            })
          }
          await runSVGO(svg)
        } catch (error) {
          console.error(`Error parsing ${name} from ${source.dir}:`, error)
          iconSet.remove(name)

          return
        }

        iconSet.fromSVG(name, svg)
      })

      allIcons.push(iconSet.export())
    }
  }

  const cssContent = allIcons
    .map(iconSet => getIconsCSS(
      iconSet,
      Object.keys(iconSet.icons),
      {
        iconSelector: '.{prefix}-{name}',
        mode: 'mask',
      },
    ))
    .join('\n')

  await fs.writeFile(target, cssContent, 'utf8')
})().catch(error => {
  console.error(error)
})

function organizeIconsList (icons: string[]): Record<string, string[]> {
  const sorted: Record<string, string[]> = Object.create(null)

  for (const icon of icons) {
    const item = stringToIcon(icon)

    if (!item) {
      continue
    }

    const prefix = item.prefix
    const prefixList = sorted[prefix] ? sorted[prefix] : (sorted[prefix] = [])

    const name = item.name

    if (!prefixList.includes(name)) {
      prefixList.push(name)
    }
  }

  return sorted
}

function resolveIconJson (prefix: string): string {
  switch (prefix) {
    case 'tabler':
      return require.resolve('@iconify-json/tabler/icons.json')
    case 'ic':
      return require.resolve('@iconify-json/ic/icons.json')
    case 'bx':
      return require.resolve('@iconify-json/bx/icons.json')
    case 'mdi':
      return require.resolve('@iconify-json/mdi/icons.json')
    default:
      return require.resolve(`@iconify/json/json/${prefix}.json`)
  }
}
