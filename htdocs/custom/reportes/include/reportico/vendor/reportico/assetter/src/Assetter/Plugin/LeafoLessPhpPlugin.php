<?php
/**
 * Copyright (c) 2016 - 2018 by Adam Banaszkiewicz
 *
 * @license   MIT License
 * @copyright Copyright (c) 2016 - 2018, Adam Banaszkiewicz
 * @link      https://github.com/requtize/assetter
 */
namespace Reportico\Assetter\Plugin;

use Reportico\Assetter\Plugin\Leafo\LessPhp\Compiler;
use Reportico\Assetter\Assetter;
use Reportico\Assetter\PluginInterface;

class LeafoLessPhpPlugin implements PluginInterface
{
    protected $filesRoot;
    protected $freshFile;
    protected $less;

    public function __construct($filesRoot)
    {
        $this->filesRoot = $filesRoot;
    }

    public function register(Assetter $assetter)
    {
        $this->freshFile = $assetter->getFreshFile();

        $assetter->listenEvent('load.all', [ $this, 'replaceAndCompile' ]);
        $assetter->listenEvent('load.css', [ $this, 'replaceAndCompile' ]);
    }

    public function replaceAndCompile(array & $groups)
    {
        foreach($groups as $kg => $group)
        {
            foreach($group['files'] as $key => $file)
            {
                if(substr($file['file'], -5, 5) === '.less')
                {
                    $groups[$kg]['files'][$key]['file']     = $this->compile($file['file']);
                    $groups[$kg]['files'][$key]['revision'] = $this->freshFile->getFilemtimeMetadata($this->filesRoot.$file['file']);
                }
            }
        }
    }

    public function compile($filepath)
    {
        $filepathRoot = $this->filesRoot.$filepath;
        $filepathNew  = str_replace('.less', '.css', $filepath);

        if($this->freshFile->isFresh($filepathRoot))
        {
            $this->preparePlugin();

            $css = $this->less->compileFile($filepathRoot);

            $this->freshFile->setRelatedFiles($filepathRoot, array_keys($this->less->allParsedFiles()));

            file_put_contents($this->filesRoot.$filepathNew, $css);
        }

        return $filepathNew;
    }

    protected function preparePlugin()
    {
        if($this->less)
            return;

        $this->less = new Compiler;
        $this->less->setFormatter('compressed');
    }
}
