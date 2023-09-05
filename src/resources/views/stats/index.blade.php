@extends('cms::layouts.backend')

@section('content')

    <div class="row">
        <div class="col-md-3">
            <div class="card border-0 bg-success">
                <div class="card-body">
                    <div class="d-flex text-white flex-wrap align-items-center">
                        <i class="fa fa-link font-size-50 mr-3"></i>
                        <div>
                            <div class="font-size-21 font-weight-bold">Links</div>
                            <div class="font-size-15">{{ trans('cms::app.total') }} Pending: {{ number_format($linkPending) }}</div>
                            <div class="font-size-15">{{ trans('cms::app.total') }} Done: {{ number_format($linkDone) }}</div>
                            <div class="font-size-15">{{ trans('cms::app.total') }} Error: {{ number_format($linkError) }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-0 bg-primary">
                <div class="card-body">
                    <div class="d-flex text-white flex-wrap align-items-center">
                        <i class="fa fa-line-chart font-size-50 mr-3"></i>
                        <div>
                            <div class="font-size-21 font-weight-bold">Contents</div>
                            <div class="font-size-15">{{ trans('cms::app.total') }} Pending: {{ number_format($contentPending) }}</div>
                            <div class="font-size-15">{{ trans('cms::app.total') }} Done: {{ number_format($contentDone) }}</div>
                            <div class="font-size-15">{{ trans('cms::app.total') }} Error: {{ number_format($contentError) }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-0 bg-info">
                <div class="card-body">
                    <div class="d-flex text-white flex-wrap align-items-center">
                        <i class="fa fa-language font-size-50 mr-3"></i>
                        <div>
                            <div class="font-size-21 font-weight-bold">Jobs</div>
                            <div class="font-size-15">{{ trans('cms::app.total') }}: {{ number_format($jobs) }}</div>
                            <div class="font-size-15">{{ trans('cms::app.total') }} Crawlers: {{ number_format($jobCrawlers) }}</div>
                            <div class="font-size-15">{{ trans('cms::app.total') }} Contents: {{ number_format($jobContents) }}</div>
                            <div class="font-size-15">{{ trans('cms::app.total') }} Translating: {{ number_format($jobTranslatings) }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-0 bg-secondary">
                <div class="card-body">
                    <div class="d-flex text-white flex-wrap align-items-center">
                        <i class="fa fa-hdd font-size-50 mr-3"></i>
                        <div>
                            <div class="font-size-21 font-weight-bold">Disk</div>
                            <div class="font-size-15">{{ trans('Free') }}: {{ $diskFree }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <canvas id="curve_chart" style="width: 100%; height: 300px"></canvas>
        </div>
    </div>

    <style>
        .card {
            min-height: 150px;
        }
    </style>

    <script type="text/javascript">
        setTimeout(function () {
            const ctx = document.getElementById('curve_chart');
            let jsonData = $.ajax({
                url: "{{ route('crawler.stats.crawler-chars') }}",
                dataType: "json",
                async: false
            }).responseText;

            jsonData = JSON.parse(jsonData);
            let labels = [];
            let crawContents = [];
            let transContents = [];
            let posts = [];

            $.each(jsonData, function (index, item) {
                labels.push(item[0]);
                crawContents.push(item[1]);
                transContents.push(item[2]);
                posts.push(item[3]);
            });

            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [
                        {
                            label: 'New Contents',
                            data: crawContents,
                            borderWidth: 1
                        },
                        {
                            label: 'New Translate Contents',
                            data: transContents,
                            borderWidth: 1
                        },
                        {
                            label: 'New Posts',
                            data: posts,
                            borderWidth: 1
                        }]
                },
                options: {
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
        }, 200);
    </script>
@endsection
