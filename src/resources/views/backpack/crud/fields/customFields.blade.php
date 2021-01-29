<!-- select2 multiple -->
@php
    //build option keys array to use with Select All in javascript.
    $model_instance = new $field['model'];
    $field['multiple'] = $field['multiple'] ?? true;
@endphp

@include('crud::fields.inc.wrapper_start')
<label>{!! $field['label'] !!}</label>
@include('crud::fields.inc.translatable_icon')
@if(isset($field['prefix']) || isset($field['suffix'])) <div class="input-group"> @endif
    @if(isset($field['prefix'])) <div class="input-group-prepend"><span class="input-group-text">{!! $field['prefix'] !!}</span></div> @endif
    <input
        type="text"
        name="{{ $field['name'] }}"
        value="{{ old(square_brackets_to_dots($field['name'])) ?? $field['value'] ?? $field['default'] ?? '' }}"
        @include('crud::fields.inc.attributes')
    >
    @if(isset($field['suffix'])) <div class="input-group-append"><span class="input-group-text">{!! $field['suffix'] !!}</span></div> @endif
    @if(isset($field['prefix']) || isset($field['suffix'])) </div> @endif

{{-- HINT --}}
    @if (isset($field['hint']))
    <p class="help-block">{!! $field['hint'] !!}</p>
    @endif


@if(isset($field['select_all']) && $field['select_all'])
    <a class="btn btn-xs btn-default select_all" style="margin-top: 5px;"><i class="la la-check-square-o"></i> {{ trans('backpack::crud.select_all') }}</a>
    <a class="btn btn-xs btn-default clear" style="margin-top: 5px;"><i class="la la-times"></i> {{ trans('backpack::crud.clear') }}</a>
@endif

{{-- HINT --}}
@if (isset($field['hint']))
    <p class="help-block">{!! $field['hint'] !!}</p>
@endif
@include('crud::fields.inc.wrapper_end')


{{-- ########################################## --}}
{{-- Extra CSS and JS for this particular field --}}
{{-- If a field type is shown multiple times on a form, the CSS and JS will only be loaded once --}}
@if ($crud->fieldTypeNotLoaded($field))
    @php
        $crud->markFieldTypeAsLoaded($field);
    @endphp

    {{-- FIELD CSS - will be loaded in the after_styles section --}}
    @push('crud_fields_styles')
        <!-- include select2 css-->
        <link href="{{ asset('packages/select2/dist/css/select2.min.css') }}" rel="stylesheet" type="text/css" />
        <link href="{{ asset('packages/select2-bootstrap-theme/dist/select2-bootstrap.min.css') }}" rel="stylesheet" type="text/css" />
    @endpush

    {{-- FIELD JS - will be loaded in the after_scripts section --}}
    @push('crud_fields_scripts')
        <!-- include select2 js-->
        <script src="{{ asset('packages/select2/dist/js/select2.full.min.js') }}"></script>
        @if (app()->getLocale() !== 'en')
            <script src="{{ asset('packages/select2/dist/js/i18n/' . app()->getLocale() . '.js') }}"></script>
        @endif
        <script>
            function bpFieldInitSelect2MultipleElement(element) {

                var $select_all = element.attr('data-select-all');
                if (!element.hasClass("select2-hidden-accessible"))
                {
                    var $obj = element.select2({
                        theme: "bootstrap"
                    });

                    //get options ids stored in the field.
                    var options = JSON.parse(element.attr('data-options-for-js'));

                    if($select_all) {
                        element.parent().find('.clear').on("click", function () {
                            $obj.val([]).trigger("change");
                        });
                        element.parent().find('.select_all').on("click", function () {
                            $obj.val(options).trigger("change");
                        });
                    }
                }
            }
        </script>
    @endpush

@endif
{{-- End of Extra CSS and JS --}}
{{-- ########################################## --}}
