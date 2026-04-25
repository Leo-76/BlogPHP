<?php
namespace App\Database;

class Paginator
{
    public readonly array $items;
    public readonly int   $total;
    public readonly int   $perPage;
    public readonly int   $currentPage;
    public readonly int   $lastPage;

    public function __construct(array $items, int $total, int $perPage, int $currentPage)
    {
        $this->items       = $items;
        $this->total       = $total;
        $this->perPage     = $perPage;
        $this->currentPage = $currentPage;
        $this->lastPage    = (int) ceil($total / $perPage);
    }

    public static function paginate(string $sql, array $params = [], int $perPage = 10, ?int $page = null): static
    {
        $page  = $page ?? (int)($_GET['page'] ?? 1);
        $page  = max(1, $page);
        $count = DB::find("SELECT COUNT(*) as n FROM ({$sql}) as sub", $params)['n'] ?? 0;
        $items = DB::query("{$sql} LIMIT {$perPage} OFFSET " . (($page - 1) * $perPage), $params);
        return new static($items, (int)$count, $perPage, $page);
    }

    public function hasPages(): bool       { return $this->lastPage > 1; }
    public function hasPrev(): bool        { return $this->currentPage > 1; }
    public function hasNext(): bool        { return $this->currentPage < $this->lastPage; }
    public function prevPage(): int        { return max(1, $this->currentPage - 1); }
    public function nextPage(): int        { return min($this->lastPage, $this->currentPage + 1); }
    public function isEmpty(): bool        { return empty($this->items); }

    public function links(string $url = '', int $window = 2): string
    {
        if (!$this->hasPages()) return '';
        $base = $url ?: strtok($_SERVER['REQUEST_URI'], '?');
        $html = '<nav class="pagination">';

        // Précédent
        if ($this->hasPrev()) {
            $html .= "<a href=\"{$base}?page={$this->prevPage()}\" class=\"page-btn\">&laquo;</a>";
        }

        // Pages
        for ($i = 1; $i <= $this->lastPage; $i++) {
            if ($i === 1 || $i === $this->lastPage || abs($i - $this->currentPage) <= $window) {
                $active = $i === $this->currentPage ? ' active' : '';
                $html .= "<a href=\"{$base}?page={$i}\" class=\"page-btn{$active}\">{$i}</a>";
            } elseif (abs($i - $this->currentPage) === $window + 1) {
                $html .= '<span class="page-dots">…</span>';
            }
        }

        // Suivant
        if ($this->hasNext()) {
            $html .= "<a href=\"{$base}?page={$this->nextPage()}\" class=\"page-btn\">&raquo;</a>";
        }

        $html .= '</nav>';
        return $html;
    }
}
