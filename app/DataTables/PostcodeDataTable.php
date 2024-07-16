<?php

namespace App\DataTables;

use App\Models\Pincode;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Html\Editor\Editor;
use Yajra\DataTables\Html\Editor\Fields;
use Yajra\DataTables\Services\DataTable;

class PostcodeDataTable extends DataTable
{
    /**
     * Build the DataTable class.
     *
     * @param QueryBuilder $query Results from query() method.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addColumn('action',function($row){
                $url = route("postcode.destroy",$row->id);
                return '<a href="'.route('postcode.edit',$row->id).'" class="btn btn-gradient-primary btn-sm"><i class="bi bi-eye-fill"></i></a>&nbsp;<a onclick=deleteData("'.route('postcode.destroy',$row->id).'") class="btn btn-gradient-danger btn-sm"><i class="bi bi-trash-fill"></i></a>';
            })->rawColumns(['action']);
    }

    /**
     * Get the query source of dataTable.
     */
    public function query(Pincode $model): QueryBuilder
    {
        return $model->newQuery();
    }

    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        return $this->builder()
                    ->setTableId('postcode-table')
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
            Column::make('stateName'),
            Column::make('pincode'),
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
        return 'Postcode_' . date('YmdHis');
    }
}
