<?php

namespace App\DataTables;

use App\Models\Review;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Html\Editor\Editor;
use Yajra\DataTables\Html\Editor\Fields;
use Yajra\DataTables\Services\DataTable;

class ReviewDataTable extends DataTable
{
    /**
     * Build the DataTable class.
     *
     * @param QueryBuilder $query Results from query() method.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->setRowId('id')
            ->addColumn('service',function($row){
                return $row->category->name;
            })
            ->addColumn('user',function($row){
                return $row->user->name;
            })
            ->addColumn('business',function($row){
                return $row->provider->name;
            })
            ->addColumn('rating',function($row){
                $rate = (int)$row->rating;
                $rating='';
                for($i=1;$i<=$rate;$i++)
                {
                    $rating.= "<i class='bi bi-star-fill text-warning'></i>";
                }
                return $rating;
            })
            ->addColumn('status',function($row){
                $statusBtn='';

                if ($row->status == 1)
                {
                    $statusBtn.= ' Active';
                }
                else
                {
                    $statusBtn.= ' Inactive';
                }
                return $statusBtn;
            })
            ->addColumn('created_at',function($row){
                return $row->created_at->format('M d, Y');
            })
            ->addColumn('action',function($row){
                return '<a href="'.route('review.view',$row->id).'" class="btn btn-gradient-primary btn-sm"><i class="bi bi-eye-fill"></i></a>&nbsp;<a onclick=deleteData("'.route('review.delete',$row->id).'") class="btn btn-gradient-danger btn-sm"><i class="bi bi-trash-fill"></i></a>';
            })->rawColumns(['action','service','user','provider','rating']);
    }

    /**
     * Get the query source of dataTable.
     */
    public function query(Review $model): QueryBuilder
    {
        return $model->orderBy('id','desc')->newQuery();
    }

    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        return $this->builder()
                    ->setTableId('review-table')
                    ->columns($this->getColumns())
                    ->minifiedAjax()
                    //->dom('Bfrtip')
                    ->orderBy(1)
                    ->selectStyleSingle()
                    ->buttons(['none']);
    }

    /**
     * Get the dataTable columns definition.
     */
    public function getColumns(): array
    {
        return [
           Column::make('id'),
            Column::computed('service'),
            Column::computed('user'),
            Column::computed('business'),
            Column::computed('rating')->exportable(false)->printable(false),
            Column::make('created_at'),
            Column::computed('action')
                  ->exportable(false)
                  ->printable(false)
                  ->width(60)
                  ->addClass('text-center'),
        ];
    }

    /**
     * Get the filename for export.
     */
    protected function filename(): string
    {
        return 'Review_' . date('YmdHis');
    }
}
