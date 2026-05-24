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
        <div class="btn-group">
            <button type="button" class="btn btn-outline-success dropdown-toggle" data-bs-toggle="dropdown">
                <i class="bi bi-download"></i> Export Data
            </button>
            <ul class="dropdown-menu">
                <li><a class="dropdown-item" href="{{ route('collections.export', [$collection, 'json']) }}">Export as JSON</a></li>
                <li><a class="dropdown-item" href="{{ route('collections.export', [$collection, 'csv']) }}">Export as CSV</a></li>
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

{{-- Custom Attributes Management --}}
<div class="card shadow-sm mb-4">
    <div class="card-header bg-white">
        <strong><i class="bi bi-tags"></i> Custom Attributes</strong>
        <span class="text-muted small ms-2">Define fields like Monthly Bill, Phone Number, etc.</span>
    </div>
    <div class="card-body">
        <form id="attributeForm" class="row g-2 align-items-end mb-3">
            @csrf
            <div class="col-md-5">
                <label class="form-label small mb-1">Attribute Name</label>
                <input type="text" name="name" class="form-control form-control-sm" placeholder="e.g. Monthly Bill" required>
            </div>
            <div class="col-md-4">
                <label class="form-label small mb-1">Type</label>
                <select name="type" class="form-select form-select-sm">
                    <option value="string">Text</option>
                    <option value="number">Number</option>
                    <option value="boolean">Yes/No</option>
                </select>
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-sm btn-primary w-100">Add Attribute</button>
            </div>
        </form>

        <div id="attributesList" class="d-flex flex-wrap gap-2">
            @forelse($collection->attributes as $attribute)
                <span class="badge bg-secondary attr-badge d-inline-flex align-items-center gap-1 py-2 px-2"
                      data-attribute-id="{{ $attribute->id }}">
                    {{ $attribute->name }}
                    <small class="opacity-75">({{ $attribute->type }})</small>
                    <button type="button" class="btn-close btn-close-white btn-sm ms-1 delete-attribute"
                            data-id="{{ $attribute->id }}" aria-label="Remove"></button>
                </span>
            @empty
                <span class="text-muted small" id="noAttributesMsg">No custom attributes yet. Add one above.</span>
            @endforelse
        </div>
    </div>
</div>

{{-- Split: Table + Map --}}
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
                            @foreach($collection->attributes as $attribute)
                                <th>{{ $attribute->name }}</th>
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
                                @foreach($collection->attributes as $attribute)
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
                                @else
                                    <input type="text" name="attributes[{{ $attribute->id }}]" class="form-control attr-input">
                                @endif
                            </div>
                        @endforeach
                    </div>
                    @if($collection->attributes->isEmpty())
                        <p class="text-muted small mb-0 mt-2" id="modalNoAttrs">Add custom attributes above to capture extra data per point.</p>
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
    .location-search-results .list-group-item {
        cursor: pointer;
        font-size: .875rem;
    }
    .location-search-results .list-group-item:hover {
        background-color: #f8f9fa;
    }
</style>
@endpush

@push('scripts')
<script>
(function () {
    const attributes = @json($collection->attributes->map(fn ($a) => ['id' => $a->id, 'name' => $a->name, 'type' => $a->type]));
    let pointsData = @json($pointsForMap);
    let map, markersLayer;
    let pickerMap, pickerMarker, pickerMapReady = false;
    let searchTimer = null;

    const defaultCenter = pointsData.length
        ? [pointsData[0].lat, pointsData[0].lng]
        : [20.5937, 78.9629];

    const urls = {
        storePoint: @json(route('collections.points.store', $collection)),
        updatePoint: (id) => @json(url('/collections/'.$collection->id.'/points/__ID__')).replace('__ID__', id),
        deletePoint: (id) => @json(url('/collections/'.$collection->id.'/points/__ID__')).replace('__ID__', id),
        storeAttribute: @json(route('collections.attributes.store', $collection)),
        deleteAttribute: (id) => @json(url('/collections/'.$collection->id.'/attributes/__ID__')).replace('__ID__', id),
        geocodeSearch: @json(route('geocode.search')),
    };

    function initMap() {
        map = L.map('map').setView(defaultCenter, pointsData.length ? 12 : 5);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap contributors'
        }).addTo(map);

        markersLayer = L.layerGroup().addTo(map);
        renderMarkers();
    }

    function initPickerMap() {
        if (pickerMapReady) {
            return;
        }

        pickerMap = L.map('pickerMap', { zoomControl: true }).setView(defaultCenter, pointsData.length ? 14 : 5);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap'
        }).addTo(pickerMap);

        pickerMarker = L.marker(defaultCenter, { draggable: true }).addTo(pickerMap);

        pickerMarker.on('dragend', function () {
            const pos = pickerMarker.getLatLng();
            updateCoordinateInputs(pos.lat, pos.lng, false);
        });

        pickerMap.on('click', function (e) {
            setPickerLocation(e.latlng.lat, e.latlng.lng, false);
        });

        pickerMapReady = true;
    }

    function updateCoordinateInputs(lat, lng, panMap) {
        $('#pointLat').val(parseFloat(lat).toFixed(8));
        $('#pointLng').val(parseFloat(lng).toFixed(8));

        if (pickerMarker) {
            pickerMarker.setLatLng([lat, lng]);
        }

        if (panMap && pickerMap) {
            pickerMap.setView([lat, lng], Math.max(pickerMap.getZoom(), 15));
        }
    }

    function setPickerLocation(lat, lng, panMap) {
        updateCoordinateInputs(lat, lng, panMap);
        $('#pickModeBadge').text('Location selected').removeClass('bg-secondary').addClass('bg-success');
    }

    function resetLocationPicker() {
        $('#locationSearch').val('');
        hideSearchResults();
        $('#pickModeBadge').text('Click map or drag marker').removeClass('bg-success').addClass('bg-primary');

        const lat = parseFloat($('#pointLat').val());
        const lng = parseFloat($('#pointLng').val());

        if (pickerMapReady) {
            if (!isNaN(lat) && !isNaN(lng)) {
                setPickerLocation(lat, lng, true);
            } else {
                pickerMap.setView(defaultCenter, pointsData.length ? 14 : 5);
                pickerMarker.setLatLng(defaultCenter);
                updateCoordinateInputs(defaultCenter[0], defaultCenter[1], false);
            }
        }
    }

    function hideSearchResults() {
        $('#locationSearchResults').addClass('d-none').empty();
    }

    function renderSearchResults(results) {
        const $list = $('#locationSearchResults').empty();

        if (!results.length) {
            $list.append('<div class="list-group-item text-muted">No locations found</div>');
        } else {
            results.forEach(function (item) {
                const $item = $('<button type="button" class="list-group-item list-group-item-action"></button>');
                $item.text(item.display_name);
                $item.on('click', function () {
                    $('#locationSearch').val(item.display_name);
                    hideSearchResults();
                    setPickerLocation(item.lat, item.lng, true);
                    if (!$('#pointName').val()) {
                        $('#pointName').val(item.display_name.split(',')[0].trim());
                    }
                });
                $list.append($item);
            });
        }

        $list.removeClass('d-none');
    }

    function searchLocation(query) {
        $.get(urls.geocodeSearch, { q: query })
            .done(function (res) {
                renderSearchResults(res.results || []);
            })
            .fail(function () {
                renderSearchResults([]);
            });
    }

    function renderMarkers() {
        markersLayer.clearLayers();
        const bounds = [];

        pointsData.forEach(function (point) {
            const marker = L.marker([point.lat, point.lng]);
            let popup = '<strong>' + escapeHtml(point.name) + '</strong><br>';
            popup += 'Lat: ' + point.lat + ', Lng: ' + point.lng;
            if (point.attributes) {
                Object.keys(point.attributes).forEach(function (key) {
                    if (point.attributes[key] !== null && point.attributes[key] !== '') {
                        popup += '<br>' + escapeHtml(key) + ': ' + escapeHtml(String(point.attributes[key]));
                    }
                });
            }
            marker.bindPopup(popup);
            marker.on('click', function () {
                highlightRow(point.id);
            });
            markersLayer.addLayer(marker);
            bounds.push([point.lat, point.lng]);
        });

        if (bounds.length > 1) {
            map.fitBounds(bounds, { padding: [30, 30] });
        } else if (bounds.length === 1) {
            map.setView(bounds[0], 14);
        }
    }

    function escapeHtml(text) {
        return $('<div>').text(text).html();
    }

    function highlightRow(pointId) {
        $('#pointsTable tbody tr').removeClass('table-primary');
        $('#pointsTable tbody tr[data-point-id="' + pointId + '"]').addClass('table-primary');
    }

    function rebuildTableHeader() {
        const $thead = $('#pointsTable thead tr');
        $thead.find('th:not(:first-child):not(:nth-child(2)):not(:nth-child(3)):not(:last-child)').remove();
        const $actions = $thead.find('th:last');
        attributes.forEach(function (attr) {
            $('<th></th>').text(attr.name).insertBefore($actions);
        });
    }

    function buildRowHtml(point) {
        let cells = '<td>' + escapeHtml(point.name) + '</td>';
        cells += '<td>' + point.lat + '</td>';
        cells += '<td>' + point.lng + '</td>';

        attributes.forEach(function (attr) {
            let val = '';
            if (point.attributes && point.attributes[attr.id]) {
                val = point.attributes[attr.id].value ?? point.attributes[attr.name] ?? '';
            } else if (point.attributes && point.attributes[attr.name] !== undefined) {
                val = point.attributes[attr.name] ?? '';
            }
            cells += '<td>' + (val !== '' && val !== null ? escapeHtml(String(val)) : '—') + '</td>';
        });

        cells += '<td class="text-nowrap">' +
            '<button type="button" class="btn btn-sm btn-outline-primary edit-point"><i class="bi bi-pencil"></i></button> ' +
            '<button type="button" class="btn btn-sm btn-outline-danger delete-point"><i class="bi bi-trash"></i></button>' +
            '</td>';

        let dataAttrs = ' data-point-id="' + point.id + '"' +
            ' data-lat="' + point.lat + '"' +
            ' data-lng="' + point.lng + '"' +
            ' data-name="' + escapeHtml(point.name) + '"';

        attributes.forEach(function (attr) {
            let val = '';
            if (point.attributes && point.attributes[attr.id]) {
                val = point.attributes[attr.id].value ?? '';
            }
            dataAttrs += ' data-attr-' + attr.id + '="' + escapeHtml(String(val ?? '')) + '"';
        });

        return '<tr' + dataAttrs + '>' + cells + '</tr>';
    }

    function syncPointsDataFromRow($row) {
        const point = {
            id: parseInt($row.data('point-id'), 10),
            name: $row.data('name'),
            lat: parseFloat($row.data('lat')),
            lng: parseFloat($row.data('lng')),
            attributes: {}
        };
        attributes.forEach(function (attr) {
            point.attributes[attr.name] = $row.data('attr-' + attr.id) ?? '';
        });
        return point;
    }

    function refreshPointsDataFromTable() {
        pointsData = [];
        $('#pointsTable tbody tr').each(function () {
            pointsData.push(syncPointsDataFromRow($(this)));
        });
        renderMarkers();
        $('#pointsCount').text(pointsData.length);
    }

    function rebuildDynamicFields(values) {
        const $container = $('#dynamicAttributeFields');
        $container.empty();
        $('#modalNoAttrs').remove();

        if (!attributes.length) {
            $container.after('<p class="text-muted small mb-0 mt-2" id="modalNoAttrs">Add custom attributes above to capture extra data per point.</p>');
            return;
        }

        attributes.forEach(function (attr) {
            const val = values && values[attr.id] ? values[attr.id].value : (values ? values[attr.id] : '');
            let field = '<div class="col-md-6 dynamic-field" data-attribute-id="' + attr.id + '">';
            field += '<label class="form-label">' + escapeHtml(attr.name) + '</label>';

            if (attr.type === 'boolean') {
                field += '<select name="attributes[' + attr.id + ']" class="form-select attr-input">';
                field += '<option value="0"' + (val == '1' ? '' : ' selected') + '>No</option>';
                field += '<option value="1"' + (val == '1' ? ' selected' : '') + '>Yes</option>';
                field += '</select>';
            } else if (attr.type === 'number') {
                field += '<input type="number" name="attributes[' + attr.id + ']" class="form-control attr-input" step="any" value="' + escapeHtml(String(val ?? '')) + '">';
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
        $('#locationSearch').val('');
        hideSearchResults();
        rebuildDynamicFields(data.values || {});

        const modal = bootstrap.Modal.getOrCreateInstance(document.getElementById('pointModal'));
        modal.show();
    }

    $('#btnAddPoint').on('click', function () {
        openPointModal('add', {});
    });

    $(document).on('click', '.edit-point', function () {
        const $row = $(this).closest('tr');
        const values = {};
        attributes.forEach(function (attr) {
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

    $('#locationSearch').on('input', function () {
        const query = $(this).val().trim();
        clearTimeout(searchTimer);

        if (query.length < 2) {
            hideSearchResults();
            return;
        }

        searchTimer = setTimeout(function () {
            searchLocation(query);
        }, 400);
    });

    $('#locationSearch').on('keydown', function (e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            const query = $(this).val().trim();
            if (query.length >= 2) {
                searchLocation(query);
            }
        }
    });

    $(document).on('click', function (e) {
        if (!$(e.target).closest('#locationSearch, #locationSearchResults').length) {
            hideSearchResults();
        }
    });

    $('#btnUseMyLocation').on('click', function () {
        const $btn = $(this);
        if (!navigator.geolocation) {
            alert('Geolocation is not supported by your browser.');
            return;
        }

        $btn.prop('disabled', true);
        navigator.geolocation.getCurrentPosition(
            function (position) {
                setPickerLocation(position.coords.latitude, position.coords.longitude, true);
                $('#locationSearch').val('Current location');
                $btn.prop('disabled', false);
            },
            function () {
                alert('Unable to retrieve your location. Please allow location access or search manually.');
                $btn.prop('disabled', false);
            },
            { enableHighAccuracy: true, timeout: 10000 }
        );
    });

    $('#pointLat, #pointLng').on('change', function () {
        const lat = parseFloat($('#pointLat').val());
        const lng = parseFloat($('#pointLng').val());
        if (!isNaN(lat) && !isNaN(lng) && pickerMapReady) {
            setPickerLocation(lat, lng, true);
        }
    });

    $('#pointForm').on('submit', function (e) {
        e.preventDefault();

        if (!$('#pointLat').val() || !$('#pointLng').val()) {
            alert('Please search or pick a location on the map.');
            return;
        }

        const pointId = $('#pointId').val();
        const url = pointId ? urls.updatePoint(pointId) : urls.storePoint;
        const method = pointId ? 'PUT' : 'POST';

        $.ajax({
            url: url,
            method: method,
            data: $(this).serialize(),
            headers: { 'Accept': 'application/json' },
            success: function (res) {
                bootstrap.Modal.getInstance(document.getElementById('pointModal')).hide();

                if (pointId) {
                    $('#pointsTable tbody tr[data-point-id="' + pointId + '"]').replaceWith(buildRowHtml(res.point));
                } else {
                    $('#emptyPointsMsg').remove();
                    $('#pointsTable tbody').append(buildRowHtml(res.point));
                }

                refreshPointsDataFromTable();
            },
            error: function (xhr) {
                let msg = 'Failed to save point.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    msg = xhr.responseJSON.message;
                } else if (xhr.responseJSON && xhr.responseJSON.errors) {
                    msg = Object.values(xhr.responseJSON.errors).flat().join('\n');
                }
                alert(msg);
            }
        });
    });

    $(document).on('click', '.delete-point', function () {
        if (!confirm('Delete this point?')) return;
        const $row = $(this).closest('tr');
        const pointId = $row.data('point-id');

        $.ajax({
            url: urls.deletePoint(pointId),
            method: 'DELETE',
            headers: { 'Accept': 'application/json' },
            success: function () {
                $row.remove();
                refreshPointsDataFromTable();
                if (!$('#pointsTable tbody tr').length) {
                    $('#pointsTable').after('<p class="text-muted text-center py-4 mb-0" id="emptyPointsMsg">No points yet. Click "Add Point" to get started.</p>');
                }
            },
            error: function () { alert('Failed to delete point.'); }
        });
    });

    $('#attributeForm').on('submit', function (e) {
        e.preventDefault();
        $.ajax({
            url: urls.storeAttribute,
            method: 'POST',
            data: $(this).serialize(),
            headers: { 'Accept': 'application/json' },
            success: function (res) {
                attributes.push(res.attribute);
                $('#noAttributesMsg').remove();
                $('#attributesList').append(
                    '<span class="badge bg-secondary attr-badge d-inline-flex align-items-center gap-1 py-2 px-2" data-attribute-id="' + res.attribute.id + '">' +
                    escapeHtml(res.attribute.name) + ' <small class="opacity-75">(' + res.attribute.type + ')</small>' +
                    '<button type="button" class="btn-close btn-close-white btn-sm ms-1 delete-attribute" data-id="' + res.attribute.id + '"></button></span>'
                );
                rebuildTableHeader();
                $('#pointsTable tbody tr').each(function () {
                    $(this).find('td:last').before('<td>—</td>');
                });
                rebuildDynamicFields({});
                $('#attributeForm')[0].reset();
            },
            error: function (xhr) {
                alert(xhr.responseJSON?.message || 'Failed to add attribute.');
            }
        });
    });

    $(document).on('click', '.delete-attribute', function () {
        if (!confirm('Remove this attribute? All stored values will be deleted.')) return;
        const id = $(this).data('id');
        const $badge = $(this).closest('[data-attribute-id]');

        $.ajax({
            url: urls.deleteAttribute(id),
            method: 'DELETE',
            headers: { 'Accept': 'application/json' },
            success: function () {
                const idx = attributes.findIndex(a => a.id === id);
                if (idx > -1) attributes.splice(idx, 1);
                $badge.remove();
                if (!attributes.length) {
                    $('#attributesList').append('<span class="text-muted small" id="noAttributesMsg">No custom attributes yet. Add one above.</span>');
                }
                location.reload();
            },
            error: function () { alert('Failed to remove attribute.'); }
        });
    });

    $(document).ready(function () {
        initMap();
        setTimeout(function () { map.invalidateSize(); }, 300);
    });

    $('#pointModal').on('shown.bs.modal', function () {
        initPickerMap();
        setTimeout(function () {
            pickerMap.invalidateSize();
            resetLocationPicker();
        }, 200);
    });

    $('#pointModal').on('hidden.bs.modal', function () {
        hideSearchResults();
    });
})();
</script>
@endpush
