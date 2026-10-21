<?php

namespace App\Service;

use PhpOffice\PhpWord\Shared\Text;
use PhpOffice\PhpWord\TemplateProcessor;

class TemplateProcessorService extends TemplateProcessor
{

    /**
     * 这方法用来解决父类 转换值为 0 的数据时  变成空的BUG
     * @param ?string $subject
     *
     * @return string
     */
    protected static function ensureUtf8Encoded($subject)
    {
        return !is_null($subject) ? Text::toUTF8($subject) : '';
    }

}
