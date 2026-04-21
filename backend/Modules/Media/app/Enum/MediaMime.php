<?php

namespace Modules\Media\Enum;

use Modules\Support\Traits\EnumArrayable;

enum MediaMime: string
{
    use EnumArrayable;

    case Avi = 'avi';
    case Csv = 'csv';
    case Doc = "doc";
    case Docx = "docx";
    case Mov = 'mov';
    case Mp4 = 'mp4';
    case Mpeg = 'mpeg';
    case Pdf = 'pdf';
    case Ppt = "ppt";
    case Pptx = "pptx";
    case Rar = 'rar';
    case Txt = 'txt';
    case Xls = 'xls';
    case Xlsx = "xlsx";
    case Zip = "zip";
    case Png = "png";
    case Jpg = "jpg";
    case Jpeg = "jpeg";
    case Svg = "svg";
    case Webp = "webp";
    case Mp3 = "mp3";
    case Wav = "wav";
    case Ogg = "ogg";
    case flac = "flac";
    case Aac = "aac";
    case M4a = "m4a";
    case Wma = "wma";
    case Webm = "webm";


    /**
     * Get mime types for validation
     *
     * @return string
     */
    public static function forValidation(): string
    {
        return implode(',', MediaMime::values());
    }
}
