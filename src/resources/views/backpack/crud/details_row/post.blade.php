<div class="m-t-10 m-b-10 p-l-10 p-r-10 p-t-10 p-b-10">
    <div class="row">
        <div class="col-md-12">
            <small>Use the <span class="label label-default">details_row</span> functionality to show more information about the entry, when that information does not fit inside the table column.</small><br><br>
            <strong>Title:</strong> {{ $entry->title }} <br>
            <strong>Description:</strong> {!! $entry->description !!} <br>
            <strong>Format:</strong> {{ $entry->format }} <br>
            <strong>created_at:</strong> {{ $entry->created_at }} <br>
            <strong>Excerpt:</strong> {{ $entry->excerpt }} <br>
            <strong>Url:</strong> {{ $entry->url }} <br>
            etc.
        </div>
    </div>
</div>
<div class="clearfix"></div>
