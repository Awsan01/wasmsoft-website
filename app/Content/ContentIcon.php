<?php

namespace App\Content;

/**
 * Icons editors can attach to services and highlights. A curated subset
 * of resources/icons/lucide.php, so every choice suits the brand style.
 */
enum ContentIcon: string
{
    case Code = 'code';
    case Mobile = 'smartphone';
    case Screen = 'monitor';
    case Cloud = 'cloud';
    case Server = 'server';
    case Database = 'database';
    case Security = 'shield-check';
    case Launch = 'rocket';
    case Idea = 'lightbulb';
    case Goal = 'target';
    case Performance = 'gauge';
    case VersionControl = 'git-branch';
    case Analytics = 'bar-chart';
    case Growth = 'trending-up';
    case Team = 'users';
    case Layers = 'layers';
    case Design = 'palette';
    case Hardware = 'cpu';
    case Maintenance = 'wrench';
    case Support = 'life-buoy';
    case Global = 'globe';
    case Speed = 'zap';
    case Quality = 'award';
    case Care = 'heart';
    case Innovation = 'sparkles';
    case Business = 'briefcase';
    case Communication = 'message-square';

    public function label(): string
    {
        return __('admin.icons.'.str_replace('-', '_', $this->value));
    }
}
