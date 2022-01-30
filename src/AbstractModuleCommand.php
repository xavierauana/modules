<?php

namespace Anacreation\Modules;

use Illuminate\Console\Command;

abstract class AbstractModuleCommand extends Command
{
    protected $modulePath = null;

    protected $replacements = [];

    /**
     * @return null
     */
    public function getModulePath()
    {
        if ($this->modulePath === null) {
            collect($this->laravel['modules']->all())
                ->each(function ($m) {
                    if ($m->getName() === $this->argument('module')) {
                        $this->modulePath = $m->getPath();
                    }
                });
        }
        return $this->modulePath;
    }

    protected function getNamespace(?string $path = null): string
    {
        $path = str_replace(base_path() . DIRECTORY_SEPARATOR, '', $path);

        $path = $this->removePhpExtension($path);

        $segments = explode(DIRECTORY_SEPARATOR, $path);

        return implode('\\', $segments);
    }

    protected function replaceStudString(string $path)
    {
        $content = file_get_contents($path);

        foreach ($this->replacements as $pattern => $r) {
            $content = str_replace($pattern, $r, $content);
        }

        file_put_contents($path, $content);
    }
    protected function addReplacement(string $pattern, string $replacement)
    {
        $this->replacements[$pattern] = $replacement;
        return $this;
    }

    public function getModelNamespaces(string $model):string
    {
        return $this->getNamespace(sprintf("%s%s%s%s%s",
            $this->getModulePath(),
            DIRECTORY_SEPARATOR,
            "Entities",
            DIRECTORY_SEPARATOR,
            $model
        ));
    }

    protected function getResourceNamespace(string $filePath):string
    {
        return $this->getNamespace($this->getModulePath().DIRECTORY_SEPARATOR."Transformers".DIRECTORY_SEPARATOR.$filePath);
    }

    protected function getRequestNamespace(string $filePath):string
    {
        $requestName = $this->removePhpExtension($filePath);

        return $this->getNamespace(sprintf("%s%s",
            $this->getModulePath().DIRECTORY_SEPARATOR.'Http'.DIRECTORY_SEPARATOR.'Requests'.DIRECTORY_SEPARATOR,
            $requestName));
    }

    protected function getShortName(string $filePath)
    {
        $segments = explode(DIRECTORY_SEPARATOR, $filePath);
        return str_replace('.php','',$segments[count($segments)-1]);
    }

    private function removePhpExtension(string $filePath):string
    {
        return str_replace('.php','',$filePath);
    }

}
