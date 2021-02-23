<?php 

namespace CloudMonitor\Toolkit\Core\Meta;

use Exception;

class Host 
{
    public function __construct()
    {
        $this->hostname = gethostname();
        $this->cpu = $this->cpu();
        $this->memory = $this->memory();
        $this->freespace = $this->freespace();
    }

    private function cpu(): ?float
    {
        try {
            return function_exists('shell_exec') && is_readable('/proc/stat')
                ? (float) shell_exec("grep 'cpu ' /proc/stat | awk '{usage=($2+$4)*100/($2+$4+$5)} END {print usage}'")
                : null;
        }
        catch (Exception $e) {
            return null;
        }
    }

    private function freespace(): ?float
    {
        try {
            return @is_readable('/')
                ? (float) round(100 - ((disk_free_space('/') / disk_total_space('/')) * 100), 2)
                : null;
        }
        catch (Exception $e) {
            return null;
        }
    }

    public function memory(): ?float
    {
        try {
            return function_exists('shell_exec') && $this->hasCommand('free')
                ? (float) shell_exec("free | grep Mem | awk '{print $3/$2 * 100.0}'")
                : null;
        }
        catch (Exception $e) {
            return null;
        }
    }

    private function hasCommand(string $command): bool 
    {
        return ! empty(
            shell_exec(sprintf("which %s", escapeshellarg($command)))
        );
    }
}