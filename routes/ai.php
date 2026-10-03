<?php

use App\Http\Middleware\EnsureMcpAccess;
use App\Mcp\Servers\ResearchServer;
use Laravel\Mcp\Facades\Mcp;

Mcp::web('/mcp/research', ResearchServer::class)->middleware(EnsureMcpAccess::class);
