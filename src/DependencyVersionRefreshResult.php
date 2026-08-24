<?php

namespace Cmsmaxinc\FilamentSystemVersions;

enum DependencyVersionRefreshResult
{
    case Refreshed;
    case AlreadyRunning;
    case Failed;
}
