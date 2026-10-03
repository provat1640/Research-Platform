<?php

namespace App\Mcp\Servers;

use App\Mcp\Tools\ProjectContextTool;
use Laravel\Mcp\Server;

class ResearchServer extends Server
{
    protected string $name = 'Co-Auth Research MCP';

    protected string $version = '1.0.0';

    protected string $instructions = 'Use these tools only for structured university research collaboration and thesis support.';

    protected array $tools = [
        ProjectContextTool::class,
    ];
}
