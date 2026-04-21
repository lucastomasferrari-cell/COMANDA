import publicRoutes from './routes/public.routes.ts'

import type {ModuleManifest} from "@/modules/core/contracts/ModuleManifest.ts";

const manifest: ModuleManifest = {
  name: 'auth',
  routes: [
    {
      target: 'public',
      routes: publicRoutes
    }
  ],
}

export default manifest
