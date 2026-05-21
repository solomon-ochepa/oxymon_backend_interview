<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class SwaggerGenerateCommand extends Command
{
    protected $signature = 'swagger:generate';

    protected $aliases = ['swagger'];

    protected $description = 'Generate the OpenAPI / Swagger documentation';

    public function handle(): int
    {
        $this->info('Generating Swagger docs...');

        $exitCode = $this->call('l5-swagger:generate');

        if ($exitCode === 0) {
            $this->info('Swagger docs generated successfully.');
        }

        return $exitCode;
    }
}
