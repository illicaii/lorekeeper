<div class="row flex-wrap">
    <div class="col-6 col-md-2">
        <div class="logs-table-cell">
            {!! $log->sender ? $log->sender->displayName : '' !!}
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="logs-table-cell">
            {!! $log->log !!}
        </div>
    </div>
    <div class="col-6 col-md-5">
        <div class="logs-table-cell">
            {!! $log->data !!}
        </div>
    </div>
    <div class="col-6 col-md-2">
        <div class="logs-table-cell">
            {!! pretty_date($log->created_at) !!}
        </div>
    </div>
</div>
