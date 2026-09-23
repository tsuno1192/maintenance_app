<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

/**
 * ステータス表示用バッジ（設備・申し送り・工具で共用）。
 */
class StatusBadge extends Component
{
    /**
     * @param  string  $label  表示テキスト
     * @param  string  $color  Tailwind カラーキー（green/red/amber/blue/slate）
     */
    public function __construct(
        public string $label,
        public string $color = 'slate',
    ) {}

    /**
     * コンポーネントビューを返す。
     */
    public function render(): View|Closure|string
    {
        return view('components.status-badge');
    }
}
