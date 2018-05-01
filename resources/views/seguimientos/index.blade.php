@extends('layouts.app')

@section('content')
    <section class="content-header">
        <h1 class="pull-left">Seguimientos</h1>
        <h1 class="pull-right">
           <a href="{{ url('seguimientos/create').'/'.$id_periodo }}" class="btn btn-primary"><i class="fa fa-plus"></i> Crear</a>

           
        </h1>
        <h1>
            <a class="btn btn-primary pull-right" style="margin-top: -10px;margin-bottom: 5px" href="{!! route('periodos.index') !!}">Atras</a>
        </h1>
    </section>
    <div class="content">
        <div class="clearfix"></div>

        @include('flash::message')

        <div class="clearfix"></div>
        <div class="box box-primary">
            <div class="box-body">
                    @include('seguimientos.table')
            </div>
        </div>
        <div class="text-center">
        
        </div>
    </div>
@endsection

