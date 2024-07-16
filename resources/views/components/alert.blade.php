<div class="col-lg-12 col-xl-12 col-md-12">
@if(Session::get('alert'))
<div class="alert alert-{{Session::get('alert')}} alert-dismissible" role="alert">
<p>{{Session::get('message')}} </p>
<!-- <button class="btn-close" data-bs-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button> -->
</div>
@endif
</div>