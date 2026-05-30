@extends('layouts.app')

@section('title', $collection->name.' — Attributes')

@section('content')
<div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-3">
    <div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-1">
                <li class="breadcrumb-item"><a href="{{ route('collections.show', $collection) }}">{{ $collection->name }}</a></li>
                <li class="breadcrumb-item active">Attributes</li>
            </ol>
        </nav>
        <h1 class="h3 mb-0">Custom Attributes</h1>
        <p class="text-muted mb-0 small">Define polymorphic fields for points and future entities.</p>
    </div>
    <a href="{{ route('collections.show', $collection) }}" class="btn btn-outline-primary">
        <i class="bi bi-map"></i> Back to Map
    </a>
</div>

<div class="card shadow-sm mb-4">
    <div class="card-header bg-white">
        <strong><i class="bi bi-plus-circle"></i> Add Attribute</strong>
    </div>
    <div class="card-body">
        <form id="createAttributeForm" class="row g-3 align-items-end">
            @csrf
            <div class="col-md-4">
                <label class="form-label">Name</label>
                <input type="text" name="name" class="form-control" placeholder="e.g. Monthly Bill" required>
            </div>
            <div class="col-md-3">
                <label class="form-label">Type</label>
                <select name="type" class="form-select">
                    <option value="string">Text</option>
                    <option value="number">Number</option>
                    <option value="date">Date</option>
                    <option value="boolean">Yes/No</option>
                </select>
            </div>
            <div class="col-md-3">
                <div class="form-check mt-4">
                    <input class="form-check-input" type="checkbox" name="is_visible" value="1" id="createVisible" checked>
                    <label class="form-check-label" for="createVisible">Visible on map &amp; export</label>
                </div>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">Add</button>
            </div>
        </form>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <strong><i class="bi bi-table"></i> Attribute Definitions</strong>
        <span class="badge bg-secondary">{{ $attributes->count() }}</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0" id="attributesTable">
                <thead class="table-light">
                    <tr>
                        <th>Name</th>
                        <th>Slug</th>
                        <th>Type</th>
                        <th class="text-center">Visible</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($attributes as $attribute)
                        <tr data-attribute-id="{{ $attribute->id }}">
                            <td class="attr-name">{{ $attribute->name }}</td>
                            <td><code class="small">{{ $attribute->slug }}</code></td>
                            <td><span class="badge bg-info text-dark attr-type">{{ $attribute->type }}</span></td>
                            <td class="text-center">
                                <div class="form-check form-switch d-inline-block">
                                    <input class="form-check-input visibility-toggle" type="checkbox"
                                           data-id="{{ $attribute->id }}"
                                           {{ $attribute->is_visible ? 'checked' : '' }}>
                                </div>
                            </td>
                            <td class="text-end text-nowrap">
                                <button type="button" class="btn btn-sm btn-outline-primary edit-attribute"
                                        data-id="{{ $attribute->id }}"
                                        data-name="{{ $attribute->name }}"
                                        data-type="{{ $attribute->type }}"
                                        data-visible="{{ $attribute->is_visible ? '1' : '0' }}">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-danger delete-attribute"
                                        data-id="{{ $attribute->id }}">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr id="noAttributesRow">
                            <td colspan="5" class="text-muted text-center py-4">No attributes defined yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="editAttributeModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="editAttributeForm">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Attribute</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="editAttributeId">
                    <div class="mb-3">
                        <label class="form-label">Name</label>
                        <input type="text" name="name" id="editAttributeName" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Type</label>
                        <select name="type" id="editAttributeType" class="form-select">
                            <option value="string">Text</option>
                            <option value="number">Number</option>
                            <option value="date">Date</option>
                            <option value="boolean">Yes/No</option>
                        </select>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="is_visible" value="1" id="editAttributeVisible">
                        <label class="form-check-label" for="editAttributeVisible">Visible on map &amp; export</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    const collectionId = @json($collection->id);
    const urls = {
        store: @json(route('collections.attributes.store', $collection)),
        update: (id) => @json(url('/collections/'.$collection->id.'/attributes/__ID__')).replace('__ID__', id),
        destroy: (id) => @json(url('/collections/'.$collection->id.'/attributes/__ID__')).replace('__ID__', id),
        visibility: (id) => @json(url('/collections/'.$collection->id.'/attributes/__ID__/visibility')).replace('__ID__', id),
    };

    function escapeHtml(text) {
        return $('<div>').text(text).html();
    }

    $('#createAttributeForm').on('submit', function (e) {
        e.preventDefault();
        $.ajax({
            url: urls.store,
            method: 'POST',
            data: $(this).serialize(),
            headers: { 'Accept': 'application/json' },
            success: function (res) {
                $('#noAttributesRow').remove();
                const a = res.attribute;
                const row = '<tr data-attribute-id="' + a.id + '">' +
                    '<td class="attr-name">' + escapeHtml(a.name) + '</td>' +
                    '<td><code class="small">' + escapeHtml(a.slug) + '</code></td>' +
                    '<td><span class="badge bg-info text-dark attr-type">' + escapeHtml(a.type) + '</span></td>' +
                    '<td class="text-center"><div class="form-check form-switch d-inline-block">' +
                    '<input class="form-check-input visibility-toggle" type="checkbox" data-id="' + a.id + '" ' + (a.is_visible ? 'checked' : '') + '></div></td>' +
                    '<td class="text-end text-nowrap">' +
                    '<button type="button" class="btn btn-sm btn-outline-primary edit-attribute" data-id="' + a.id + '" data-name="' + escapeHtml(a.name) + '" data-type="' + a.type + '" data-visible="' + (a.is_visible ? '1' : '0') + '"><i class="bi bi-pencil"></i></button> ' +
                    '<button type="button" class="btn btn-sm btn-outline-danger delete-attribute" data-id="' + a.id + '"><i class="bi bi-trash"></i></button>' +
                    '</td></tr>';
                $('#attributesTable tbody').append(row);
                $('#createAttributeForm')[0].reset();
                $('#createVisible').prop('checked', true);
            },
            error: function (xhr) {
                alert(xhr.responseJSON?.message || 'Failed to create attribute.');
            }
        });
    });

    $(document).on('click', '.edit-attribute', function () {
        $('#editAttributeId').val($(this).data('id'));
        $('#editAttributeName').val($(this).data('name'));
        $('#editAttributeType').val($(this).data('type'));
        $('#editAttributeVisible').prop('checked', $(this).data('visible') == 1);
        bootstrap.Modal.getOrCreateInstance(document.getElementById('editAttributeModal')).show();
    });

    $('#editAttributeForm').on('submit', function (e) {
        e.preventDefault();
        const id = $('#editAttributeId').val();
        $.ajax({
            url: urls.update(id),
            method: 'PUT',
            data: $(this).serialize(),
            headers: { 'Accept': 'application/json' },
            success: function (res) {
                const a = res.attribute;
                const $row = $('tr[data-attribute-id="' + a.id + '"]');
                $row.find('.attr-name').text(a.name);
                $row.find('.attr-type').text(a.type);
                $row.find('.visibility-toggle').prop('checked', a.is_visible);
                $row.find('.edit-attribute')
                    .data('name', a.name)
                    .data('type', a.type)
                    .data('visible', a.is_visible ? '1' : '0');
                bootstrap.Modal.getInstance(document.getElementById('editAttributeModal')).hide();
            },
            error: function (xhr) {
                alert(xhr.responseJSON?.message || 'Failed to update attribute.');
            }
        });
    });

    $(document).on('change', '.visibility-toggle', function () {
        const id = $(this).data('id');
        const $toggle = $(this);
        $.ajax({
            url: urls.visibility(id),
            method: 'PATCH',
            headers: { 'Accept': 'application/json' },
            success: function (res) {
                $toggle.prop('checked', res.attribute.is_visible);
                $('tr[data-attribute-id="' + id + '"] .edit-attribute')
                    .data('visible', res.attribute.is_visible ? '1' : '0');
            },
            error: function () {
                $toggle.prop('checked', !$toggle.prop('checked'));
                alert('Failed to update visibility.');
            }
        });
    });

    $(document).on('click', '.delete-attribute', function () {
        if (!confirm('Delete this attribute? All stored values will be removed.')) return;
        const id = $(this).data('id');
        const $row = $(this).closest('tr');
        $.ajax({
            url: urls.destroy(id),
            method: 'DELETE',
            headers: { 'Accept': 'application/json' },
            success: function () {
                $row.remove();
                if (!$('#attributesTable tbody tr').length) {
                    $('#attributesTable tbody').append(
                        '<tr id="noAttributesRow"><td colspan="5" class="text-muted text-center py-4">No attributes defined yet.</td></tr>'
                    );
                }
            },
            error: function () { alert('Failed to delete attribute.'); }
        });
    });
})();
</script>
@endpush
