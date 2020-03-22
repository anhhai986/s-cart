@extends('admin.layout')

@section('main')

<div class="row">
@php
  $infosDescription = $obj->descriptions;
@endphp
  <div class="col-md-12">
    <div class="box box-primary">

      <div class="box-header with-border">
        <div class="pull-right">
        </div>
        <div class="pull-left">
          <input id="maintain_mode" data-on-text="{{ trans('admin.maintain_enable') }}" data-off-text="{{ trans('admin.maintain_disable') }}" type="checkbox"  {{ (sc_config('SITE_STATUS') == 'off'?'checked':'') }}>
         </div>
        <!-- /.box-tools -->
      </div>


        @foreach ($infosDescription as  $infoDescription)
              <div class="box-header with-border">
                <h3 class="box-title">{{ trans('store_info.maintain_content') }} {{ $languages[$infoDescription['lang']] }}</h3>
              </div>
              <div class="box-body table-responsive no-padding box-primary">
                {!! sc_html_render($infoDescription['maintain_content']) !!}
              </div>
        @endforeach
    </div>
  </div>

</div>


@endsection


@push('styles')

@endpush

@push('scripts')

    {{-- //Pjax --}}
   <script src="{{ asset('admin/plugin/jquery.pjax.js')}}"></script>

  <script type="text/javascript">

    $(document).on('pjax:send', function() {
      $('#loading').show()
    })
    $(document).on('pjax:complete', function() {
      $('#loading').hide()
    })
    $(document).ready(function(){
    // does current browser support PJAX
      if ($.support.pjax) {
        $.pjax.defaults.timeout = 2000; // time in milliseconds
      }
    });

    $(document).on('ready pjax:end', function(event) {
//
    })

  </script>
    {{-- //End pjax --}}

    <script type="text/javascript">
      $("#maintain_mode").bootstrapSwitch();
      $('#maintain_mode').on('switchChange.bootstrapSwitch', function (event, state) {
          var data_config;
          if (state == true) {
              data_config =  'on';
          } else {
              data_config = 'off';
          }
          $('#loading').show()
          $.ajax({
            type: 'POST',
            dataType:'json',
            url: "urlUpdate",
            data: {
              "_token": "$csrf_token",
              "name": "maintain_mode",
              "value": data_config
            },
            success: function (response) {
                console.log(response);
              if(parseInt(response.error) ==0){
                  alertMsg(response.msg, '', 'success');
              }else{
                  alertMsg(response.msg, '', 'error');
              }
              $('#loading').hide();
            }
          });
      }); 
  
  </script>

@endpush
