@extends('admin.layout')

@section('main')
   <div class="row">
      <div class="col-md-12">
        <div class="nav-tabs-custom">
        <ul class="nav nav-tabs">
          <li class=""><a href="{{ route('admin_plugin', ['code' => strtolower($code)]) }}">{{ trans('admin.plugin_manager.local') }}</a></li>
          <li class="active"><a href="#">{{ trans('admin.plugin_manager.online') }}</a></li>
          <li class="pull-right">{!! trans('admin.extensions_more') !!}</li>
        </ul>
            <!-- /.box-header -->
          <section id="pjax-container" class="table-list">
            <div class="box-body table-responsive no-padding">
              <table id="plugin" class="table table-bordered table-hover">
                <thead>
                <tr>
                  <th>{{ trans('admin.plugin_manager.image') }}</th>
                  <th>{{ trans('admin.plugin_manager.code') }}</th>
                  <th>{{ trans('admin.plugin_manager.name') }}</th>
                  <th>{{ trans('admin.plugin_manager.version') }}</th>
                  <th>{{ trans('admin.plugin_manager.auth') }}</th>
                  <th>{{ trans('admin.plugin_manager.link') }}</th>
                  <th>{{ trans('admin.plugin_manager.price') }}</th>
                  <th>{{ trans('admin.plugin_manager.rated') }}</th>
                  <th>{{ trans('admin.plugin_manager.downloaded') }}</th>
                  <th>{{ trans('admin.plugin_manager.date') }}</th>
                  <th>{{ trans('admin.plugin_manager.action') }}</th>
                </tr>
                </thead>
                <tbody>
                  @if (!$arrPluginLibrary)
                    <tr>
                      <td colspan="5" style="text-align: center;color: red;">
                        {{ trans('admin.plugin_manager.empty') }}
                      </td>
                    </tr>
                  @else
                    @foreach ($arrPluginLibrary as  $plugin)
                    @php
                      if (array_key_exists($plugin['key'], $arrPluginLocal)) {
                        $pluginAction = trans('admin.plugin_manager.located');
                      } else {
                        $pluginAction = '<span onClick="installPlugin($(this),\''.$plugin['key'].'\', \''.$plugin['path'].'\');" title="'.trans('admin.plugin_manager.install').'" type="button" class="btn btn-flat btn-success"><i class="fa fa-plus-circle"></i></span>';
                      }
                    @endphp

                      <tr>
                        <td>{!! sc_image_render($plugin['image'],'50px', '', $plugin['name']) !!}</td>
                        <td>{{ $plugin['key'] }}</td>
                        <td>{{ $plugin['name'] }} <span data-toggle="tooltip" title="{!! $plugin['description'] !!}"><i class="fa fa-info-circle" aria-hidden="true"></i></span></td>
                        <td>{{ $plugin['version']??'' }}</td>
                        <td>{{ $plugin['username']??'' }}</td>
                        <td><a target=_new href="{{ $plugin['link'] }}"><i class="fa fa-chain-broken" aria-hidden="true"></i> {!! trans('admin.plugin_manager.link') !!}</a></td>
                        <td>{!! $plugin['is_free']? '<span class="label label-success">'.trans('admin.plugin_manager.free').'</span>' : $plugin['price'] !!}</td>
                        <td>
                          @php
                          $vote = $plugin['points'];
                          $vote_times = $plugin['times'];
                          $cal_vote = $vote_times?round($vote/$vote_times,1):0;
                          @endphp
                          <span title="{{ $cal_vote }}" style="color:#e66c16">
                            @for ($i = 1; $i <= $cal_vote; $i++) 
                            <i class="fa fa-star voted" aria-hidden="true"></i>
                            @endfor
                            @if ($cal_vote * $vote_times == $vote)
                               <i class="fa fa-star-o" aria-hidden="true"></i>
                            @else
                               <i class="fa fa-star-half-o voted" aria-hidden="true"></i>
                            @endif
                            @for ($k = 1; $k < (5-$cal_vote); $k++) 
                            <i class="fa fa-star-o" aria-hidden="true"></i>
                            @endfor
                         </span>
                         <span class="sum_vote">
                          ({{ $vote }}/{{ $vote_times }})
                        </span>

                        </td>
                        <td>{{ $plugin['downloaded']??'' }}</td>
                        <td>{{ $plugin['date']??'' }}</td>
                        <td>{!! $pluginAction ?? '' !!}</td>
                      </tr>
                    @endforeach
                  @endif
                </tbody>
              </table>
            </div>
            <div class="box-footer clearfix">
              {!! $resultItems??'' !!}
              <ul class="pagination pagination-sm no-margin pull-right">
                <!-- Previous Page Link -->
                    @if ($dataApi['current_page'] > 1)
                    <li class="page-item"><a class="page-link pjax-container" href="{{ route('admin_plugin_online', ['code' => strtolower($code)]) }}?page={{ $dataApi['current_page'] - 1}}" rel="prev">«</a></li>
                    @endif
                    @for ($i = 1; $i < $dataApi['last_page']; $i++)
                        @if ( $dataApi['current_page'] == $i)
                        <li class="page-item active"><span class="page-link pjax-container">{{ $i }}</span></li>
                        @else
                        <li class="page-item"><a class="page-link" href="{{ route('admin_plugin_online', ['code' => strtolower($code)]) }}?page={{ $i }}">{{ $i }}</a></li>
                        @endif
                    @endfor
                    @if ($dataApi['current_page'] < $dataApi['last_page'])
                    <li class="page-item"><a class="page-link pjax-container" href="{{ route('admin_plugin_online', ['code' => strtolower($code)]) }}?page={{ $dataApi['current_page'] + 1}}" rel="next">»</a></li>

                    @endif
                </ul>
           </div>
          </section>
            <!-- /.box-body -->
          </div>
        </div>
      </div>
@endsection

@push('scripts')
{{-- //Pjax --}}
<script src="{{ asset('admin/plugin/jquery.pjax.js')}}"></script>


<script type="text/javascript">
  function installPlugin(obj,key, path) {
      $('#loading').show()
      obj.button('loading');
      $.ajax({
        type: 'POST',
        dataType:'json',
        url: '{{ route('admin_plugin_online.install') }}',
        data: {
          "_token": "{{ csrf_token() }}",
          "key":key,
          "path":path,
          "code":"{{ $code }}"
        },
        success: function (response) {
          console.log(response);
              if(parseInt(response.error) ==0){
              location.reload();
              }else{
                Swal.fire(
                  response.msg,
                  'You clicked the button!',
                  'error'
                  )
              }
              $('#loading').hide();
              obj.button('reset');
        }
      });
  }

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
    // tag a
    $(function(){
     $(document).pjax('a.page-link', '#pjax-container')
    })

    $(document).on('ready pjax:end', function(event) {
      //
    })

    $('[data-toggle="tooltip"]').tooltip();
</script>

@endpush
