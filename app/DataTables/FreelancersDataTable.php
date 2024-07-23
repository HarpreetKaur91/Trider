<?php

namespace App\DataTables;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Html\Editor\Editor;
use Yajra\DataTables\Html\Editor\Fields;
use Yajra\DataTables\Services\DataTable;

class FreelancersDataTable extends DataTable
{
    /**
     * Build the DataTable class.
     *
     * @param QueryBuilder $query Results from query() method.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return datatables()
            ->eloquent($query)
            ->setRowId('id')
            ->editColumn('created_at',function($row){
                return $row->created_at->format('M d, Y');
            })
            ->editColumn('account_status',function($row){
                if (is_null($row->account_status))
                {
                    return "<label class='badge badge-gradient-info'>Pending</label>";
                }
                elseif($row->account_status == 1)
                {
                    return "<label class='badge badge-gradient-info'>Approved</label>";
                }
                else
                {
                    return " <label class='badge badge-gradient-danger'>Declined</label>";
                }
            })
            ->addColumn('action',function($row){
                return '<a href="'.route('freelancer.view',$row->id).'" class="btn btn-gradient-primary btn-sm"><i class="bi bi-eye-fill"></i></a>&nbsp;<a onclick=deleteData("'.route('freelancer.destroy',$row->id).'") class="btn btn-gradient-danger btn-sm"><i class="bi bi-trash-fill"></i></a>';
            })->rawColumns(['account_status','action']);
    }

    /**
     * Get the query source of dataTable.
     */
    public function query(User $model): QueryBuilder
    {
        return $model::whereHas('roles',function($q){ $q->where('role_name','freelancer'); })->newQuery();
    }

    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        return $this->builder()
                    ->setTableId('freelancers-table')
                    ->columns($this->getColumns())
                    ->minifiedAjax()
                    //->dom('Bfrtip')
                    ->orderBy(1)
                    ->parameters([
                        'buttons' => ['none'],
                    ]);
    }

    /**
     * Get the dataTable columns definition.
     */
    public function getColumns(): array
    {
        return [
            Column::make('id'),
            Column::make('name'),
            Column::make('email'),
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
        return 'Freelancers_' . date('YmdHis');
    }
}
