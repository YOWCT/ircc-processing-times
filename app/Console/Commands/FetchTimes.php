<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\ParserRun;

class FetchTimes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'times:fetch';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetches the remote set of IRCC processing times, and parses it.';

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
     * @return mixed
     */
    public function handle()
    {

        $startDate = date('Y-m-d H:i:s');
        echo "Starting at ". $startDate . " \n";


        // Create a new ParserRun and run it!
        $runner = new ParserRun;
        $totalUpdatedRows = $runner->run();

        echo "\n\n...started at " . $startDate . "\n";
        echo "Finished with " . $totalUpdatedRows . " updated rows at ". date('Y-m-d H:i:s') . " \n\n";
    }
}
