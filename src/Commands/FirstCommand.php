<?php

namespace Anacreation\Modules\Commands;

use Anacreation\Modules\AbstractModuleCommand;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Str;

class FirstCommand extends AbstractModuleCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mm:model {model} {module} {--api} {--plain}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {

        $this->overwriteConfig();

        $this->createModel();

        $this->createModelFactory();

        $this->createPolicy();

        $this->createRequest();

        $this->option('api') ?
            $this->createResources() :
            $this->createViews();

        $this->createController();

        $this->info(sprintf("%s model is created for module: %s!",
            $this->argument('model'),
            $this->argument('module')));

        return 0;
    }

    private function createResources()
    {

        $resourceName = sprintf('%s%sResource',
            $this->argument('model'),
            DIRECTORY_SEPARATOR);

        $resourceCollectionName = sprintf('%s%sCollectionResource',
            $this->argument('model'),
            DIRECTORY_SEPARATOR);


        Artisan::call('module:make-resource', [
            'name' => $resourceName,
            'module' => $this->argument('module'),
        ]);

        Artisan::call('module:make-resource', [
            'name' => $resourceCollectionName,
            'module' => $this->argument('module'),
            '--collection' => true
        ]);

        $resourceNamespace = $this->getResourceNamespace($resourceName);
        $resourceCollectionNamespace = $this->getResourceNamespace($resourceCollectionName);

        $this->addReplacement('$COLLECTIONRESOURCENAMESPACE$', $resourceCollectionNamespace);
        $this->addReplacement('$RESOURCENAMESPACE$', $resourceNamespace);
        $this->addReplacement('$COLLECTIONRESOURCE$', $resourceCollectionName);
        $this->addReplacement('$RESOURCE$',$resourceName);
    }

    private function createViews()
    {
    }

    private function overwriteConfig()
    {
        config(['modules.stubs' => config('anamodules.stubs')]);
    }

    private function createController()
    {

        $this->info('stub path is ' . config('modules.stubs.path'));

        $controller = sprintf('%sController', ucwords($this->argument('model')));

        Artisan::call('module:make-controller', [
            'controller' => $controller,
            'module' => $this->argument('module'),
            '--api' => $this->option('api'),
            '--plain' => $this->option('plain')
        ]);


        $controllerPath = $this->getControllerPath($controller);

        $this->getNamespace($controllerPath);

        $this->replaceStudString($controllerPath);

    }



    /**
     * @param string $controller
     * @return string
     */
    private function getControllerPath(string $controller): string
    {
        return sprintf("%s%sHttp%sControllers%s%s.php",
            $this->getModulePath(),
            DIRECTORY_SEPARATOR,
            DIRECTORY_SEPARATOR,
            DIRECTORY_SEPARATOR,
            $controller);
    }

    /**
     * @return void
     */
    private function createModel(): void
    {
        Artisan::call('module:make-model', [
            'model' => $this->argument('model'),
            'module' => $this->argument('module'),
            '--migration' => true
        ]);

        $this->addReplacement('$MODELNAMESPACE$', $this->getModelNamespaces($this->argument('model')));
        $this->addReplacement('$MODEL$', ucwords($this->argument('model')));
        $this->addReplacement('$model$',  strtolower($this->argument('model')));
    }

    /**
     * @return void
     */
    private function createModelFactory(): void
    {
        Artisan::call('module:make-factory', [
            'name' => $this->argument('model'),
            'module' => $this->argument('module')
        ]);
    }

    /**
     * @return void
     */
    private function createPolicy(): void
    {
        Artisan::call('module:make-policy', [
            'name' => $this->argument('model') . "Policy",
            'module' => $this->argument('module')
        ]);
    }

    private function createRequest():void
    {
        $storeRequestName = sprintf("%s%s%s",$this->argument('model'),DIRECTORY_SEPARATOR,"StoreRequest");
        $updateRequestName = sprintf("%s%s%s",$this->argument('model'),DIRECTORY_SEPARATOR,"UpdateRequest");
        Artisan::call('module:make-request', [
            'name' => $storeRequestName,
            'module' => $this->argument('module'),
        ]);
        Artisan::call('module:make-request', [
            'name' => $updateRequestName,
            'module' => $this->argument('module'),
        ]);

        $this->addReplacement('$UPDATEREQUEST$', $updateRequestName);
        $this->addReplacement('$STOREEQUEST$', $storeRequestName);
        $this->addReplacement('$STOREREQUESTNAMESPACE$', $this->getRequestNamespace($storeRequestName));
        $this->addReplacement('$UPDATEREQUESTNAMESPACE$', $this->getRequestNamespace($updateRequestName));
    }
}
