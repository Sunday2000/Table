<?php 

namespace App;

use App\URLHelper;

class Table
{
    private $query;

    private $get;

    private $sortable = [];

    private $columns = [];

    private $formatters = [];

    private $limit = 20;

    const SORT_KEY = 'sort';
    const DIR_KEY ="dir";

    public function __construct(QueryBuilder $query, array $get)
    {
        $this->query = $query;
        $this->get = $get;
    }
    
    /**
     * Specify sortable columns, it's can sorted by ASC OR DESC
     *
     * @param  mixed $sortable
     * @return self
     */
    public function sortable(string ...$sortable):self
    {
        $this->sortable = $sortable;
        return $this;
    }
    
    /**
     * Take associative array with database attributes 
     * you would like to show and his name in table
     * ['city' => 'City']
     *
     * @param  mixed $columns
     * @return self
     */
    public function columns(array $columns):self
    {
        $this->columns = $columns;
        return $this;
    }
    
    /**
     * format an attribute using a callable
     *
     * @param  string $key
     * @param  callable $function
     * @return self
     */
    public function format(string $key, callable $function):self
    {
        $this->formatters[$key] = $function;
        return $this;
    }
    
    /**
     * Set the return records number
     *
     * @param  int $limit
     * @return self
     */
    public function setLimit(int $limit):self
    {
        if ($limit > 0){
            $this->limit = $limit;
        }
        return $this;
    }

    private function th(string $sortKey)
    {
        if ( !in_array($sortKey, $this->sortable) ){
            return $this->columns[$sortKey];
        }
        $sort = $this->get[self::SORT_KEY] ?? null;
        $direction = $this->get[self::DIR_KEY] ?? null;
        $icon = "";
        if ( $sort === $sortKey ){
            $icon = $direction === "asc" ? "^" : "v";
        }
        $url = URLHelper::withParams([
            "sort" => $sortKey,
            "dir" => $direction === 'asc' && $sort === $sortKey ? "desc" : "asc"
        ], $this->get);
        return <<<HTML
        <a href="? $url">{$this->columns[$sortKey]} $icon</a>
HTML;
    }


    private function td(string $key, array $item)
    {
        if ( isset($this->formatters[$key]) ){
            return $this->formatters[$key]($item[$key]);
        }
        return  $item[$key];
    }
    
    /**
     * Build the table and return it
     *
     * @return string
     */
    public function render():string
    {
        $get = $this->get;
        $page = $get['p'] ?? 1;
        $count = (clone $this->query)->count();

        if ( !empty($get['sort']) && in_array($get['sort'], $this->sortable)){
            $this->query->orderBY($get['sort'], $get['dir'] ?? 'asc');
        }
        $items = $this->query
            ->select(array_keys($this->columns))
            ->limit($this->limit)
            ->page($page)
            ->fetchAll();
        $pages = ceil($count / $this->limit);
        ob_start();
        ?>
            <table class="table table-striped">
                <thead>
                    <tr>
                    <?php foreach($this->columns as $column => $value): ?>
                        <th><?= $this->th($column) ?></th>
                    <?php endforeach ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($items as $item): ?>
                        <tr>
                            <?php foreach($this->columns as $column => $value): ?>
                                <td><?= $this->td($column, $item) ?></td>
                            <?php endforeach ?>
                        </tr>
                    <?php endforeach ?>
                </tbody>
            </table>
            <?php if ($page > 1): ?>
                <a href="?<?=URLHelper::withParam($this->get, "p", $page - 1) ?>" class="btn btn-primary">Page précedente</a>
            <?php endif ?>
            <?php if($page < $pages): ?>
                <a href="?<?= URLHelper::withParam($this->get, "p", $page + 1) ?>" class="btn btn-primary">Page suivante</a>
            <?php endif ?>
        <?php
        return ob_get_clean();
    }
}