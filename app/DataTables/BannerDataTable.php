<?php

namespace App\DataTables;

use App\Models\Banner;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Html\Editor\Editor;
use Yajra\DataTables\Html\Editor\Fields;
use Yajra\DataTables\Services\DataTable;

class BannerDataTable extends DataTable
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
            ->addColumn('banner_type',function($row){
                return ucwords(strtolower($row->banner_type));
            })
            ->addColumn('created_at',function($row){
                return $row->created_at->format('M d, Y');
            })
            ->addColumn('image',function($row){
                if(!is_null($row->image)){
                    $url = \Storage::url($row->image);
                    $assetLink =  asset($url);
                }
                else{
                    $assetLink = asset('empty.jpg');
                }
                
                return '<img src="'.$assetLink.'" class="me-2" alt="image">';
            })
            ->addColumn('action',function($row){
                return '<a href="'.route('banner.edit',$row->id).'" class="btn btn-gradient-primary btn-sm"><i class="bi bi-eye-fill"></i></a>&nbsp;<a onclick=deleteData("'.route('banner.destroy',$row->id).'") class="btn btn-gradient-danger btn-sm"><i class="bi bi-trash-fill"></i></a>';
            })->rawColumns(['action','image']);
    }

    /**
     * Get the query source of dataTable.
     */
    public function query(Banner $model): QueryBuilder
    {
        return $model->newQuery();
    }

    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        return $this->builder()
                    ->setTableId('banner-table')
                    ->columns($this->getColumns())
                    ->minifiedAjax()
                    //->dom('Bfrtip')
                    ->orderBy(1)
                    ->selectStyleSingle()
                    ->parameters([
                        'buttons' => ['add'],
                    ]);
    }

    /**
     * Get the dataTable columns definition.
     */
    public function getColumns(): array
    {
        return [
            Column::make('id'),
            Column::make('banner_type'),
            Column::make('image'),
            Column::make('status'),
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
        return 'Banner_' . date('YmdHis');
    }
}
