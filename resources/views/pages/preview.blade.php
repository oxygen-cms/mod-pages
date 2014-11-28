@extends(Config::get('oxygen/core::layout'))

<?php
    //$usePage = false;
?>

@section('content')

@include('oxygen/crud::versionable.itemHeader', ['blueprint' => $blueprint, 'item' => $item, 'title' => Lang::get('oxygen/pages::ui.preview'), 'seamless' => false])

<div class="Block Block--noPadding">

@include('oxygen/pages::pages.previewBox')

</div>

@include('oxygen/crud::versionable.versions', ['item' => $item])

@stop