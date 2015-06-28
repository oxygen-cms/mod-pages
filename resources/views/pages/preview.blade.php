@extends(app('oxygen.layout'))

<?php
    //$usePage = false;
?>

@section('content')

@include('oxygen/crud::versionable.itemHeader', ['blueprint' => $blueprint, 'item' => $item, 'title' => Lang::get('oxygen/mod-pages::ui.preview')])

<div class="Block Block--noPadding">

@include('oxygen/mod-pages::pages.previewBox')

</div>

@include('oxygen/crud::versionable.versions', ['item' => $item])

@stop