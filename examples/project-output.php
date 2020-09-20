@extends('documentation.base')

@section('content')
          @include('documentation.framework-section-start')

                  <div class="highlight" style="background-color: #eeeeee">
                  <code>
<?php
            highlight_string('<?php
          \Reportico\Engine\Builder::build()
          ->newSession()
          ->project("tutorials")
          ->load("stock")
          ->execute();
?>
            ');
?>
                  </code></pre>
                  </div>

                <div>
                <p>
                </div>

<?php
\Reportico\Engine\Builder::build()
          ->project     ("tutorials")
          ->load     ("stock")
          ->execute();
?>
          @include('documentation.framework-section-end')

@endsection

@section('javascript')

    <!--script src="{{ asset('js/Chart.min.js') }}"></script-->
    <!--script src="{{ asset('js/coreui-chartjs.bundle.js') }}"></script-->
    <script src="{{ asset('js/main.js') }}" defer></script>
@endsection
