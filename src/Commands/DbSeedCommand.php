<?php

namespace CommercePunditTech\LaravelSeeding\Commands;

use Illuminate\Console\Command;
use Illuminate\Console\ConfirmableTrait;
use Illuminate\Database\ConnectionResolverInterface as Resolver;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Symfony\Component\Console\Input\InputOption;

class DbSeedCommand extends Command
{
    use ConfirmableTrait;

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'cpt:db:seed';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Seed the database with records By Commerce Pundit Tech.';

    /**
     * The connection resolver instance.
     *
     * @var \Illuminate\Database\ConnectionResolverInterface
     */
    protected $resolver;

    /**
     * Create a new database seed command instance.
     *
     * @param  \Illuminate\Database\ConnectionResolverInterface  $resolver
     * @return void
     */
    public function __construct(Resolver $resolver)
    {
        parent::__construct();

        $this->resolver = $resolver;
    }


    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        if (! $this->confirmToProceed()) {
            return;
        }

        $this->resolver->setDefaultConnection($this->getDatabase());

        Model::unguarded(function () {
            $this->getSeeder()->__invoke();
            $this->runSeeder();
        });

        $this->info('CPT::Database seeding completed successfully.');
    }

    /**
     * Get a seeder instance from the container.
     *
     * @return \Illuminate\Database\Seeder
     */
    protected function getSeeder()
    {
        $class = $this->laravel->make($this->input->getOption('class'));

        return $class->setContainer($this->laravel)->setCommand($this);
    }

    /**
     * Get the name of the database connection to use.
     *
     * @return string
     */
    protected function getDatabase()
    {
        $database = $this->input->getOption('database');

        return $database ?: $this->laravel['config']['database.default'];
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return [
            ['class', null, InputOption::VALUE_OPTIONAL, 'The class name of the root seeder', 'DatabaseSeeder'],

            ['database', null, InputOption::VALUE_OPTIONAL, 'The database connection to seed'],

            ['force', null, InputOption::VALUE_NONE, 'Force the operation to run when in production'],
        ];
    }

    public function runSeeder()
    {
        $tableNames = config('laravelseeding.table_names');
        try {
            DB::beginTransaction();
            // Getting seeder database record
            $seederrecord = DB::table($tableNames['seeder'])->get()->pluck('seeder_status', 'seeder_name')->toArray();
            foreach (File::allFiles(base_path('database/seeds/class')) as $file) {
                $class = $this->get_class_from_file($file->getPathname());
                $fileName = pathinfo($file->getFilename(), PATHINFO_FILENAME);

                if (isset($seederrecord[$fileName]) && $seederrecord[$fileName] == 1) {
                    continue;
                }
                if (isset($seederrecord[$fileName])) {
                    if ($seederrecord[$fileName] == 1) {
                        DB::table($tableNames['seeder'])->insert([
                            'seeder_name' => $fileName,
                            'seeder_status' => 1
                        ]);
                    } else {
                        DB::table($tableNames['seeder'])
                            ->where('seeder_name', '=', $fileName)
                            ->where('seeder_status', '=', 0)
                            ->update([
                                'seeder_status' => 1
                            ]);
                    }
                } else {
                    DB::table($tableNames['seeder'])->insert([
                        'seeder_name' => $fileName,
                        'seeder_status' => 1
                    ]);
                }

                Artisan::call('db:seed', ['--class' => $class, '--no-interaction' => true]);
            }
            DB::commit();
        } catch (\Exception $e) {
            // if anything goes wrong remove file.
            DB::rollBack();
            // Revert all db changes if error raise in above code.
            dd($e->getMessage());
        }

    }
    public function get_class_from_file($path_to_file)
    {
        //Grab the contents of the file
        $contents = file_get_contents($path_to_file);

        //Start with a blank namespace and class
        $namespace = $class = "";

        //Set helper values to know that we have found the namespace/class token and need to collect the string values after them
        $getting_namespace = $getting_class = false;

        //Go through each token and evaluate it as necessary
        foreach (token_get_all($contents) as $token) {

            //If this token is the namespace declaring, then flag that the next tokens will be the namespace name
            if (is_array($token) && $token[0] == T_NAMESPACE) {
                $getting_namespace = true;
            }

            //If this token is the class declaring, then flag that the next tokens will be the class name
            if (is_array($token) && $token[0] == T_CLASS) {
                $getting_class = true;
            }

            //While we're grabbing the namespace name...
            if ($getting_namespace === true) {

                //If the token is a string or the namespace separator...
                if (is_array($token) && in_array($token[0], [T_STRING, T_NS_SEPARATOR])) {

                    //Append the token's value to the name of the namespace
                    $namespace .= $token[1];
                } elseif ($token === ';') {

                    //If the token is the semicolon, then we're done with the namespace declaration
                    $getting_namespace = false;
                }
            }

            //While we're grabbing the class name...
            if ($getting_class === true) {

                //If the token is a string, it's the name of the class
                if (is_array($token) && $token[0] == T_STRING) {

                    //Store the token's value as the class name
                    $class = $token[1];

                    //Got what we need, stope here
                    break;
                }
            }
        }

        //Build the fully-qualified class name and return it
        return $namespace ? $namespace . '\\' . $class : $class;
    }

}
