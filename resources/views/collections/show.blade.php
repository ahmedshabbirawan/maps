@extends('layouts.app')

@section('title', $collection->name)

@section('content')
<div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-3">
    <div>
        <h1 class="h3 mb-1">{{ $collection->name }}</h1>
        @if($collection->description)
            <p class="text-muted mb-0">{{ $collection->description }}</p>
        @endif
    </div>
    <div class="d-flex flex-wrap gap-2">
        <a href="{{ route('collections.attributes.index', $collection) }}" class="btn btn-outline-dark">
            <i class="bi bi-tags"></i> Manage Attributes
        </a>
        <div class="btn-group">
            <button type="button" class="btn btn-outline-success dropdown-toggle" data-bs-toggle="dropdown">
                <i class="bi bi-download"></i> Export
            </button>
            <ul class="dropdown-menu">
                <li><a class="dropdown-item export-link" href="{{ route('collections.export', [$collection, 'json']) }}" data-format="json">Export JSON</a></li>
                <li><a class="dropdown-item export-link" href="{{ route('collections.export', [$collection, 'csv']) }}" data-format="csv">Export CSV</a></li>
            </ul>
        </div>
        <button type="button" class="btn btn-primary" id="btnAddPoint">
            <i class="bi bi-plus-lg"></i> Add Point
        </button>
        <a href="{{ route('collections.edit', $collection) }}" class="btn btn-outline-secondary">
            <i class="bi bi-pencil"></i> Edit
        </a>
        <form action="{{ route('collections.destroy', $collection) }}" method="POST"
              onsubmit="return confirm('Delete this collection and all its points?');">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-outline-danger"><i class="bi bi-trash"></i></button>
        </form>
    </div>
</div>

{{-- Dynamic filter builder --}}
<div class="card shadow-sm mb-4">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <strong><i class="bi bi-funnel"></i> Filter Points</strong>
        <button type="button" class="btn btn-sm btn-outline-secondary" id="btnResetFilters">Reset</button>
    </div>
    <div class="card-body">
        <div id="filterRows">
            <div class="row g-2 align-items-end filter-row mb-2">
                <div class="col-md-4">
                    <label class="form-label small mb-1">Attribute</label>
                    <select class="form-select form-select-sm filter-attribute">
                        <option value="">— Select attribute —</option>
                        @foreach($filterableAttributes as $attr)
                            <option value="{{ $attr['id'] }}" data-type="{{ $attr['type'] }}">{{ $attr['name'] }} ({{ $attr['type'] }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 filter-operator-col">
                    <label class="form-label small mb-1">Operator</label>
                    <select class="form-select form-select-sm filter-operator">
                        <option value="=">=</option>
                    </select>
                </div>
                <div class="col-md-4 filter-value-col">
                    <label class="form-label small mb-1">Value</label>
                    <input type="text" class="form-control form-control-sm filter-value" placeholder="Value">
                </div>
                <div class="col-md-2">
                    <button type="button" class="btn btn-sm btn-outline-danger w-100 remove-filter d-none">Remove</button>
                </div>
            </div>
        </div>
        <div class="d-flex gap-2 mt-2">
            <button type="button" class="btn btn-sm btn-outline-primary" id="btnAddFilter">
                <i class="bi bi-plus"></i> Add Filter
            </button>
            <button type="button" class="btn btn-sm btn-primary" id="btnApplyFilters">
                <i class="bi bi-search"></i> Apply Filters
            </button>
        </div>
        <p class="text-muted small mb-0 mt-2">Filter by custom attribute values. Map and table update via AJAX.</p>
    </div>
</div>

<div class="row g-3 split-map">
    <div class="col-lg-6">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <strong><i class="bi bi-table"></i> Points</strong>
                <span class="badge bg-primary" id="pointsCount">{{ $collection->points->count() }}</span>
            </div>
            <div class="card-body p-0 table-responsive" style="max-height: 520px; overflow-y: auto;">
                <table class="table table-sm table-hover mb-0" id="pointsTable">
                    <thead class="table-light sticky-top">
                        <tr>
                            <th>Name</th>
                            <th>Lat</th>
                            <th>Lng</th>
                            @foreach($visibleAttributes as $attribute)
                                <th data-attr-id="{{ $attribute->id }}">{{ $attribute->name }}</th>
                            @endforeach
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($collection->points as $point)
                            <tr data-point-id="{{ $point->id }}"
                                data-lat="{{ $point->lat }}"
                                data-lng="{{ $point->lng }}"
                                data-name="{{ $point->name }}"
                                @foreach($collection->attributes as $attribute)
                                    data-attr-{{ $attribute->id }}="{{ $point->valueForAttribute($attribute->id) }}"
                                @endforeach
                            >
                                <td>{{ $point->name }}</td>
                                <td>{{ $point->lat }}</td>
                                <td>{{ $point->lng }}</td>
                                @foreach($visibleAttributes as $attribute)
                                    <td>{{ $point->valueForAttribute($attribute->id) ?? '—' }}</td>
                                @endforeach
                                <td class="text-nowrap">
                                    <button type="button" class="btn btn-sm btn-outline-primary edit-point"><i class="bi bi-pencil"></i></button>
                                    <button type="button" class="btn btn-sm btn-outline-danger delete-point"><i class="bi bi-trash"></i></button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                @if($collection->points->isEmpty())
                    <p class="text-muted text-center py-4 mb-0" id="emptyPointsMsg">No points yet. Click "Add Point" to get started.</p>
                @endif
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-white">
                <strong><i class="bi bi-map"></i> Map View</strong>
            </div>
            <div class="card-body p-2">
                <div id="map"></div>
            </div>
        </div>
    </div>
</div>

{{-- Point Modal --}}
<div class="modal fade" id="pointModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <form id="pointForm">
                <div class="modal-header">
                    <h5 class="modal-title" id="pointModalTitle">Add Point</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="pointId" value="">

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Point Name</label>
                            <input type="text" name="name" id="pointName" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Search Location</label>
                            <div class="position-relative">
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                                    <input type="text" id="locationSearch" class="form-control"
                                           placeholder="Search address, city, landmark..." autocomplete="off">
                                    <button type="button" class="btn btn-outline-secondary" id="btnUseMyLocation" title="Use my current location">
                                        <i class="bi bi-crosshair"></i>
                                    </button>
                                </div>
                                <div id="locationSearchResults" class="list-group location-search-results d-none"></div>
                            </div>
                        </div>
                    </div>

                    <div class="card mt-3 border">
                        <div class="card-header bg-light py-2 d-flex justify-content-between align-items-center">
                            <span class="small fw-semibold"><i class="bi bi-geo-alt"></i> Pick location on map</span>
                            <span class="badge bg-primary" id="pickModeBadge">Click map or drag marker</span>
                        </div>
                        <div class="card-body p-2">
                            <div id="pickerMap"></div>
                        </div>
                    </div>

                    <div class="row g-3 mt-1">
                        <div class="col-md-6">
                            <label class="form-label">Latitude</label>
                            <input type="number" name="lat" id="pointLat" class="form-control" step="any" min="-90" max="90" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Longitude</label>
                            <input type="number" name="lng" id="pointLng" class="form-control" step="any" min="-180" max="180" required>
                        </div>
                    </div>

                    <hr class="my-3">
                    <div id="dynamicAttributeFields" class="row g-3">
                        @foreach($collection->attributes as $attribute)
                            <div class="col-md-6 dynamic-field" data-attribute-id="{{ $attribute->id }}" data-type="{{ $attribute->type }}">
                                <label class="form-label">{{ $attribute->name }}</label>
                                @if($attribute->type === 'boolean')
                                    <select name="attributes[{{ $attribute->id }}]" class="form-select attr-input">
                                        <option value="0">No</option>
                                        <option value="1">Yes</option>
                                    </select>
                                @elseif($attribute->type === 'number')
                                    <input type="number" name="attributes[{{ $attribute->id }}]" class="form-control attr-input" step="any">
                                @elseif($attribute->type === 'date')
                                    <input type="date" name="attributes[{{ $attribute->id }}]" class="form-control attr-input">
                                @else
                                    <input type="text" name="attributes[{{ $attribute->id }}]" class="form-control attr-input">
                                @endif
                            </div>
                        @endforeach
                    </div>
                    @if($collection->attributes->isEmpty())
                        <p class="text-muted small mb-0 mt-2" id="modalNoAttrs">
                            <a href="{{ route('collections.attributes.index', $collection) }}">Define custom attributes</a> to capture extra data per point.
                        </p>
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="pointSubmitBtn">Save Point</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    #pickerMap { height: 320px; border-radius: .375rem; z-index: 0; }
    .location-search-results {
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        z-index: 1060;
        max-height: 220px;
        overflow-y: auto;
        box-shadow: 0 .5rem 1rem rgba(0,0,0,.15);
    }
    .location-search-results .list-group-item { cursor: pointer; font-size: .875rem; }
    .location-search-results .list-group-item:hover { background-color: #f8f9fa; }
</style>
@endpush

@push('scripts')
<script>
(function () {
    const allAttributes = @json($allAttributesForJs);
    const visibleAttributes = @json($visibleAttributesForJs);
    const filterableAttributes = @json($filterableAttributes);
    let pointsData = @json($pointsForMap);
    let map, markersLayer;
    let pickerMap, pickerMarker, pickerMapReady = false;
    let searchTimer = null;
    let activeFilters = [];

    const defaultCenter = pointsData.length
        ? [pointsData[0].lat, pointsData[0].lng]
        : [20.5937, 78.9629];

    const urls = {
        listPoints: @json(route('collections.points.index', $collection)),
        storePoint: @json(route('collections.points.store', $collection)),
        updatePoint: (id) => @json(url('/collections/'.$collection->id.'/points/__ID__')).replace('__ID__', id),
        deletePoint: (id) => @json(url('/collections/'.$collection->id.'/points/__ID__')).replace('__ID__', id),
        exportBase: @json(route('collections.export', [$collection, 'json'])).replace(/\/json$/, ''),
        geocodeSearch: @json(route('geocode.search')),
    };

    const operatorsByType = {
        string: [
            { v: '=', l: 'equals' },
            { v: 'contains', l: 'contains' },
            { v: '!=', l: 'not equals' },
        ],
        number: [
            { v: '=', l: '=' },
            { v: '>', l: '>' },
            { v: '<', l: '<' },
            { v: '>=', l: '>=' },
            { v: '<=', l: '<=' },
            { v: '!=', l: '!=' },
        ],
        date: [
            { v: '=', l: 'on' },
            { v: '>', l: 'after' },
            { v: '<', l: 'before' },
            { v: '>=', l: 'on or after' },
            { v: '<=', l: 'on or before' },
            { v: 'between', l: 'between' },
        ],
        boolean: [{ v: '=', l: 'is' }],
    };

    function escapeHtml(text) {
        return $('<div>').text(text).html();
    }

    function getAttrMeta(id) {
        return filterableAttributes.find(a => a.id == id);
    }

    function updateFilterValueInput($row) {
        const attrId = $row.find('.filter-attribute').val();
        const meta = getAttrMeta(attrId);
        const $col = $row.find('.filter-value-col');
        const $opCol = $row.find('.filter-operator-col');
        const $op = $row.find('.filter-operator');

        $op.empty();
        if (!meta) {
            $col.html('<label class="form-label small mb-1">Value</label><input type="text" class="form-control form-control-sm filter-value" placeholder="Value">');
            $op.append('<option value="=">=</option>');
            return;
        }

        (operatorsByType[meta.type] || operatorsByType.string).forEach(function (op) {
            $op.append('<option value="' + op.v + '">' + op.l + '</option>');
        });

        let inputHtml = '<label class="form-label small mb-1">Value</label>';
        if (meta.type === 'boolean') {
            inputHtml += '<select class="form-select form-select-sm filter-value"><option value="1">Yes</option><option value="0">No</option></select>';
        } else if (meta.type === 'date') {
            inputHtml += '<input type="date" class="form-control form-control-sm filter-value">';
            if ($op.val() === 'between') {
                inputHtml += '<input type="date" class="form-control form-control-sm filter-value-end mt-1" placeholder="End date">';
            }
        } else if (meta.type === 'number') {
            inputHtml += '<input type="number" class="form-control form-control-sm filter-value" step="any">';
        } else {
            inputHtml += '<input type="text" class="form-control form-control-sm filter-value" placeholder="Value">';
        }
        $col.html(inputHtml);

        $op.off('change.filterBetween').on('change.filterBetween', function () {
            if (meta.type === 'date' && $(this).val() === 'between') {
                if (!$col.find('.filter-value-end').length) {
                    $col.append('<input type="date" class="form-control form-control-sm filter-value-end mt-1">');
                }
            } else {
                $col.find('.filter-value-end').remove();
            }
        });
    }

    function collectFilters() {
        const filters = [];
        $('#filterRows .filter-row').each(function () {
            const attrId = $(this).find('.filter-attribute').val();
            if (!attrId) return;
            const meta = getAttrMeta(attrId);
            const operator = $(this).find('.filter-operator').val();
            let value = $(this).find('.filter-value').val();
            if (meta && meta.type === 'date' && operator === 'between') {
                value = {
                    start: value,
                    end: $(this).find('.filter-value-end').val(),
                };
            }
            filters.push({ attribute_id: parseInt(attrId, 10), operator: operator, value: value });
        });
        return filters;
    }

    function buildFilterQuery() {
        const filters = collectFilters();
        const params = new URLSearchParams();
        filters.forEach(function (f, i) {
            params.append('filters[' + i + '][attribute_id]', f.attribute_id);
            params.append('filters[' + i + '][operator]', f.operator);
            if (typeof f.value === 'object') {
                params.append('filters[' + i + '][value][start]', f.value.start || '');
                params.append('filters[' + i + '][value][end]', f.value.end || '');
            } else {
                params.append('filters[' + i + '][value]', f.value);
            }
        });
        return params.toString();
    }

    function updateExportLinks() {
        const qs = buildFilterQuery();
        $('.export-link').each(function () {
            const format = $(this).data('format');
            $(this).attr('href', urls.exportBase + '/' + format + (qs ? '?' + qs : ''));
        });
    }

    function applyFilters() {
        activeFilters = collectFilters();
        updateExportLinks();
        const qs = buildFilterQuery();
        $.get(urls.listPoints + (qs ? '?' + qs : ''), function (res) {
            renderPointsTable(res.points);
            pointsData = res.points.map(function (p) {
                const attrs = {};
                Object.keys(p.attributes || {}).forEach(function (key) {
                    const a = p.attributes[key];
                    if (a.is_visible) {
                        attrs[a.name] = a.value;
                    }
                });
                return { id: p.id, name: p.name, lat: p.lat, lng: p.lng, attributes: attrs };
            });
            renderMarkers();
            $('#pointsCount').text(res.count);
        });
    }

    function formatAttrDisplay(attr, raw) {
        if (raw === null || raw === '') return '—';
        if (attr.type === 'boolean') return raw === '1' ? 'Yes' : 'No';
        return escapeHtml(String(raw));
    }

    function renderPointsTable(points) {
        const $tbody = $('#pointsTable tbody').empty();
        $('#emptyPointsMsg').remove();

        if (!points.length) {
            $('#pointsTable').after('<p class="text-muted text-center py-4 mb-0" id="emptyPointsMsg">No points match the current filters.</p>');
            return;
        }

        points.forEach(function (point) {
            let cells = '<td>' + escapeHtml(point.name) + '</td>';
            cells += '<td>' + point.lat + '</td><td>' + point.lng + '</td>';
            visibleAttributes.forEach(function (attr) {
                const val = point.attributes[attr.id]?.value;
                cells += '<td>' + formatAttrDisplay(attr, val) + '</td>';
            });
            cells += '<td class="text-nowrap">' +
                '<button type="button" class="btn btn-sm btn-outline-primary edit-point"><i class="bi bi-pencil"></i></button> ' +
                '<button type="button" class="btn btn-sm btn-outline-danger delete-point"><i class="bi bi-trash"></i></button></td>';

            let dataAttrs = ' data-point-id="' + point.id + '" data-lat="' + point.lat + '" data-lng="' + point.lng + '" data-name="' + escapeHtml(point.name) + '"';
            allAttributes.forEach(function (attr) {
                const val = point.attributes[attr.id]?.value ?? '';
                dataAttrs += ' data-attr-' + attr.id + '="' + escapeHtml(String(val)) + '"';
            });
            $tbody.append('<tr' + dataAttrs + '>' + cells + '</tr>');
        });
    }

    function initMap() {
        map = L.map('map').setView(defaultCenter, pointsData.length ? 12 : 5);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap contributors'
        }).addTo(map);
        markersLayer = L.layerGroup().addTo(map);
        renderMarkers();
    }

    function renderMarkers() {
        markersLayer.clearLayers();
        const bounds = [];
        pointsData.forEach(function (point) {
            const marker = L.marker([point.lat, point.lng]);
            let popup = '<strong>' + escapeHtml(point.name) + '</strong><br>Lat: ' + point.lat + ', Lng: ' + point.lng;
            if (point.attributes) {
                Object.keys(point.attributes).forEach(function (key) {
                    const v = point.attributes[key];
                    if (v !== null && v !== '') {
                        popup += '<br>' + escapeHtml(key) + ': ' + escapeHtml(String(v));
                    }
                });
            }
            marker.bindPopup(popup);
            marker.on('click', function () { highlightRow(point.id); });
            markersLayer.addLayer(marker);
            bounds.push([point.lat, point.lng]);
        });
        if (bounds.length > 1) map.fitBounds(bounds, { padding: [30, 30] });
        else if (bounds.length === 1) map.setView(bounds[0], 14);
    }

    function highlightRow(pointId) {
        $('#pointsTable tbody tr').removeClass('table-primary');
        $('#pointsTable tbody tr[data-point-id="' + pointId + '"]').addClass('table-primary');
    }

    function initPickerMap() {
        if (pickerMapReady) return;
        pickerMap = L.map('pickerMap').setView(defaultCenter, pointsData.length ? 14 : 5);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution: '&copy; OpenStreetMap' }).addTo(pickerMap);
        pickerMarker = L.marker(defaultCenter, { draggable: true }).addTo(pickerMap);
        pickerMarker.on('dragend', function () {
            const pos = pickerMarker.getLatLng();
            updateCoordinateInputs(pos.lat, pos.lng, false);
        });
        pickerMap.on('click', function (e) { setPickerLocation(e.latlng.lat, e.latlng.lng, false); });
        pickerMapReady = true;
    }

    function updateCoordinateInputs(lat, lng, panMap) {
        $('#pointLat').val(parseFloat(lat).toFixed(8));
        $('#pointLng').val(parseFloat(lng).toFixed(8));
        if (pickerMarker) pickerMarker.setLatLng([lat, lng]);
        if (panMap && pickerMap) pickerMap.setView([lat, lng], Math.max(pickerMap.getZoom(), 15));
    }

    function setPickerLocation(lat, lng, panMap) {
        updateCoordinateInputs(lat, lng, panMap);
        $('#pickModeBadge').text('Location selected').removeClass('bg-secondary').addClass('bg-success');
    }

    function hideSearchResults() { $('#locationSearchResults').addClass('d-none').empty(); }

    function rebuildDynamicFields(values) {
        const $container = $('#dynamicAttributeFields').empty();
        $('#modalNoAttrs').remove();
        if (!allAttributes.length) {
            $container.after('<p class="text-muted small mb-0 mt-2" id="modalNoAttrs">Define custom attributes to capture extra data.</p>');
            return;
        }
        allAttributes.forEach(function (attr) {
            const val = values && values[attr.id] ? values[attr.id].value : '';
            let field = '<div class="col-md-6"><label class="form-label">' + escapeHtml(attr.name) + '</label>';
            if (attr.type === 'boolean') {
                field += '<select name="attributes[' + attr.id + ']" class="form-select attr-input">';
                field += '<option value="0"' + (val == '1' ? '' : ' selected') + '>No</option>';
                field += '<option value="1"' + (val == '1' ? ' selected' : '') + '>Yes</option></select>';
            } else if (attr.type === 'number') {
                field += '<input type="number" name="attributes[' + attr.id + ']" class="form-control attr-input" step="any" value="' + escapeHtml(String(val ?? '')) + '">';
            } else if (attr.type === 'date') {
                field += '<input type="date" name="attributes[' + attr.id + ']" class="form-control attr-input" value="' + escapeHtml(String(val ?? '')) + '">';
            } else {
                field += '<input type="text" name="attributes[' + attr.id + ']" class="form-control attr-input" value="' + escapeHtml(String(val ?? '')) + '">';
            }
            field += '</div>';
            $container.append(field);
        });
    }

    function openPointModal(mode, data) {
        $('#pointModalTitle').text(mode === 'edit' ? 'Edit Point' : 'Add Point');
        $('#pointSubmitBtn').text(mode === 'edit' ? 'Update Point' : 'Save Point');
        $('#pointId').val(data.id || '');
        $('#pointName').val(data.name || '');
        $('#pointLat').val(data.lat || '');
        $('#pointLng').val(data.lng || '');
        rebuildDynamicFields(data.values || {});
        bootstrap.Modal.getOrCreateInstance(document.getElementById('pointModal')).show();
    }

    $(document).on('change', '.filter-attribute', function () {
        updateFilterValueInput($(this).closest('.filter-row'));
    });

    $('#btnAddFilter').on('click', function () {
        const $first = $('#filterRows .filter-row').first().clone();
        $first.find('.filter-attribute').val('');
        $first.find('.remove-filter').removeClass('d-none');
        $('#filterRows').append($first);
        updateFilterValueInput($first);
    });

    $(document).on('click', '.remove-filter', function () {
        $(this).closest('.filter-row').remove();
        if ($('#filterRows .filter-row').length === 0) {
            $('#btnAddFilter').click();
        }
    });

    $('#btnResetFilters').on('click', function () {
        $('#filterRows').html($('#filterRows .filter-row').first().clone());
        $('#filterRows .filter-row').find('.filter-attribute').val('');
        $('#filterRows .filter-row').find('.remove-filter').addClass('d-none');
        updateFilterValueInput($('#filterRows .filter-row'));
        activeFilters = [];
        applyFilters();
    });

    $('#btnAddPoint').on('click', function () { openPointModal('add', {}); });

    $(document).on('click', '.edit-point', function () {
        const $row = $(this).closest('tr');
        const values = {};
        allAttributes.forEach(function (attr) {
            values[attr.id] = { value: $row.data('attr-' + attr.id) };
        });
        openPointModal('edit', {
            id: $row.data('point-id'),
            name: $row.data('name'),
            lat: $row.data('lat'),
            lng: $row.data('lng'),
            values: values,
        });
    });

    $('#pointForm').on('submit', function (e) {
        e.preventDefault();
        const pointId = $('#pointId').val();
        $.ajax({
            url: pointId ? urls.updatePoint(pointId) : urls.storePoint,
            method: pointId ? 'PUT' : 'POST',
            data: $(this).serialize(),
            headers: { 'Accept': 'application/json' },
            success: function () {
                bootstrap.Modal.getInstance(document.getElementById('pointModal')).hide();
                applyFilters();
            },
            error: function (xhr) {
                alert(xhr.responseJSON?.message || 'Failed to save point.');
            }
        });
    });

    $(document).on('click', '.delete-point', function () {
        if (!confirm('Delete this point?')) return;
        const pointId = $(this).closest('tr').data('point-id');
        $.ajax({
            url: urls.deletePoint(pointId),
            method: 'DELETE',
            headers: { 'Accept': 'application/json' },
            success: function () { applyFilters(); },
            error: function () { alert('Failed to delete point.'); }
        });
    });

    $('#btnApplyFilters').on('click', applyFilters);

    $('#locationSearch').on('input', function () {
        const query = $(this).val().trim();
        clearTimeout(searchTimer);
        if (query.length < 2) { hideSearchResults(); return; }
        searchTimer = setTimeout(function () {
            $.get(urls.geocodeSearch, { q: query }).done(function (res) {
                const $list = $('#locationSearchResults').empty();
                (res.results || []).forEach(function (item) {
                    $('<button type="button" class="list-group-item list-group-item-action"></button>')
                        .text(item.display_name)
                        .on('click', function () {
                            $('#locationSearch').val(item.display_name);
                            hideSearchResults();
                            setPickerLocation(item.lat, item.lng, true);
                        })
                        .appendTo($list);
                });
                $list.removeClass('d-none');
            });
        }, 400);
    });

    $('#btnUseMyLocation').on('click', function () {
        if (!navigator.geolocation) { alert('Geolocation not supported.'); return; }
        navigator.geolocation.getCurrentPosition(
            function (pos) { setPickerLocation(pos.coords.latitude, pos.coords.longitude, true); },
            function () { alert('Unable to get location.'); }
        );
    });

    $('#pointLat, #pointLng').on('change', function () {
        const lat = parseFloat($('#pointLat').val());
        const lng = parseFloat($('#pointLng').val());
        if (!isNaN(lat) && !isNaN(lng) && pickerMapReady) setPickerLocation(lat, lng, true);
    });

    $(document).ready(function () {
        initMap();
        updateExportLinks();
        setTimeout(function () { map.invalidateSize(); }, 300);
    });

    $('#pointModal').on('shown.bs.modal', function () {
        initPickerMap();
        setTimeout(function () { pickerMap.invalidateSize(); }, 200);
    });
})();
</script>
@endpush
