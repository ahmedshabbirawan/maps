@extends('layouts.app')

@section('title', $collection->name.' — Attributes')

@section('breadcrumb')
    <li><a href="{{ route('collections.index') }}">Dashboard</a></li>
    <li class="separator">/</li>
    <li><a href="{{ route('collections.show', $collection) }}">{{ $collection->name }}</a></li>
    <li class="separator">/</li>
    <li>Attributes</li>
@endsection

@section('page-title', 'Custom Attributes')
@section('page-subtitle', 'Define polymorphic fields for points and future entities')

@section('page-actions')
    <a href="{{ route('collections.show', $collection) }}" class="btn btn-kt-primary btn-sm">
        <i class="bi bi-map me-1"></i> Back to Map
    </a>
@endsection

@section('content')
<div class="kt-card mb-4">
    <div class="kt-card-header">
        <h2 class="card-title"><i class="bi bi-plus-circle text-primary"></i> Add Attribute</h2>
    </div>
    <div class="kt-card-body">
        <form id="createAttributeForm" class="row g-3 align-items-end">
            @csrf
            <div class="col-md-4">
                <label class="form-label fw-semibold">Name</label>
                <input type="text" name="name" class="form-control" placeholder="e.g. Monthly Bill" required>
            </div>
            <div class="col-md-3">
                <label class="form-label fw-semibold">Type</label>
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
                <button type="submit" class="btn btn-kt-primary w-100">Add</button>
            </div>
        </form>
    </div>
</div>

<div class="kt-card">
    <div class="kt-card-header">
        <h2 class="card-title"><i class="bi bi-table text-primary"></i> Attribute Definitions</h2>
        <span class="kt-badge kt-badge-secondary">{{ $attributes->count() }}</span>
    </div>
    <div class="kt-card-body p-0">
        <div class="table-responsive">
            <table class="table kt-table mb-0" id="attributesTable">
                <thead>
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
                            <td class="attr-name fw-semibold">{{ $attribute->name }}</td>
                            <td><code class="small">{{ $attribute->slug }}</code></td>
                            <td><span class="kt-badge kt-badge-primary attr-type">{{ $attribute->type }}</span></td>
                            <td class="text-center">
                                <div class="form-check form-switch d-inline-block">
                                    <input class="form-check-input visibility-toggle" type="checkbox"
                                           data-id="{{ $attribute->id }}"
                                           {{ $attribute->is_visible ? 'checked' : '' }}>
                                </div>
                            </td>
                            <td class="text-end text-nowrap">
                                <button type="button" class="btn btn-sm btn-kt-light edit-attribute"
                                        data-id="{{ $attribute->id }}"
                                        data-name="{{ $attribute->name }}"
                                        data-type="{{ $attribute->type }}"
                                        data-visible="{{ $attribute->is_visible ? '1' : '0' }}">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-kt-light text-danger delete-attribute"
                                        data-id="{{ $attribute->id }}">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr id="noAttributesRow">
                            <td colspan="5" class="text-muted text-center py-5">No attributes defined yet.</td>
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
                    <h5 class="modal-title fw-bold">Edit Attribute</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="editAttributeId">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Name</label>
                        <input type="text" name="name" id="editAttributeName" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Type</label>
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
                    <button type="button" class="btn btn-kt-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-kt-primary">Save Changes</button>
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
                    '<td class="attr-name fw-semibold">' + escapeHtml(a.name) + '</td>' +
                    '<td><code class="small">' + escapeHtml(a.slug) + '</code></td>' +
                    '<td><span class="kt-badge kt-badge-primary attr-type">' + escapeHtml(a.type) + '</span></td>' +
                    '<td class="text-center"><div class="form-check form-switch d-inline-block">' +
                    '<input class="form-check-input visibility-toggle" type="checkbox" data-id="' + a.id + '" ' + (a.is_visible ? 'checked' : '') + '></div></td>' +
                    '<td class="text-end text-nowrap">' +
                    '<button type="button" class="btn btn-sm btn-kt-light edit-attribute" data-id="' + a.id + '" data-name="' + escapeHtml(a.name) + '" data-type="' + a.type + '" data-visible="' + (a.is_visible ? '1' : '0') + '"><i class="bi bi-pencil"></i></button> ' +
                    '<button type="button" class="btn btn-sm btn-kt-light text-danger delete-attribute" data-id="' + a.id + '"><i class="bi bi-trash"></i></button>' +
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
                        '<tr id="noAttributesRow"><td colspan="5" class="text-muted text-center py-5">No attributes defined yet.</td></tr>'
                    );
                }
            },
            error: function () { alert('Failed to delete attribute.'); }
        });
    });
})();
</script>
@endpush
