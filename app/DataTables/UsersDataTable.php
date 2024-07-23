<?php

namespace App\DataTables;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class UsersDataTable extends DataTable
{
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        //return (new EloquentDataTable($query))->setRowId('id');
        return datatables()
            ->eloquent($query)
            ->setRowId('id')
            ->editColumn('created_at',function($row){
                return $row->created_at->format('M d, Y');
            })
            ->editColumn('report_status',function($row){
                if(!is_null($row->report_status))
                {
                    return "<label class='badge badge-gradient-info text-capitalize'>".$row->report_status."</label>";
                }
            })
            ->addColumn('action',function($row){
                return '<a href="'.route('customer.view',$row->id).'" class="btn btn-gradient-primary btn-sm"><i class="bi bi-eye-fill"></i></a>&nbsp;<a onclick=deleteData("'.route('customer.destroy',$row->id).'") class="btn btn-gradient-danger btn-sm"><i class="bi bi-trash-fill"></i></a>';
            })->rawColumns(['report_status','action']);
    }

    public function query(User $model): QueryBuilder
    {
        return $model::whereHas('roles',function($q){ $q->where('role_name','user'); })->newQuery();
    }

    public function html(): HtmlBuilder
    {
        return $this->builder()
                    ->setTableId('users-table')
                    ->columns($this->getColumns())
                    ->minifiedAjax()
                    ->orderBy(1)
                    ->parameters([
                        'buttons' => ['none'],
                    ]);
                    // ->buttons([
                    //     Button::make('add'),
                    //     Button::make('excel'),
                    //     Button::make('csv'),
                    //     Button::make('pdf'),
                    //     Button::make('print'),
                    //     Button::make('reset'),
                    //     Button::make('reload'),
                    // ]);
    }

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

    protected function filename(): string
    {
        return 'Users_'.date('YmdHis');
    }
}
