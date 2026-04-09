<div class="table-responsive">
  <table class="table table-vcenter table-hover card-table mb-0" id="fm-table">
    <thead>
      <tr>
        <th style="width:1%"><input type="checkbox" id="fm-check-all" class="form-check-input m-0" onchange="fmCheckAll(this)"></th>
        <th>Ad</th>
        <th style="width:100px">Boyut</th>
        <th style="width:130px">Değiştirilme</th>
        <th style="width:80px">İzin</th>
        <th style="width:70px">Sahip</th>
        <th style="width:1%"></th>
      </tr>
    </thead>
    <tbody id="fm-tbody">
      @forelse($files as $file)
        <tr data-path="{{ $file->path }}" data-isdir="{{ $file->is_dir ? '1' : '0' }}"
            ondblclick="{{ $file->is_dir ? 'fmBrowse(\''.addslashes($file->path).'\')' : ($file->editable ? 'fmEdit(\''.addslashes($file->path).'\')' : 'fmDownload(\''.addslashes($file->path).'\')') }}">
          <td>
            @if(!$file->is_parent)
              <input type="checkbox" class="form-check-input m-0 fm-row-check" value="{{ $file->path }}">
            @endif
          </td>
          <td>
            @if($file->is_dir)
              <a href="#" onclick="fmBrowse('{{ addslashes($file->path) }}'); return false;"
                 class="d-flex align-items-center gap-2 text-reset text-decoration-none">
                <i class="ti {{ $file->icon }} fs-4"></i>
                <span>{{ $file->name }}</span>
              </a>
            @else
              <span class="d-flex align-items-center gap-2">
                <i class="ti {{ $file->icon }} fs-4"></i>
                <span>{{ $file->name }}</span>
              </span>
            @endif
          </td>
          <td class="text-muted">{{ $file->size_human }}</td>
          <td class="text-muted small">{{ $file->modified }}</td>
          <td>
            @if(!$file->is_parent)
              <code class="small cursor-pointer" onclick="fmChmod('{{ addslashes($file->path) }}','{{ $file->permissions }}')"
                    title="Değiştir">{{ $file->permissions }}</code>
            @endif
          </td>
          <td class="text-muted small">{{ $file->owner }}</td>
          <td class="text-end">
            @if(!$file->is_parent)
              <div class="dropdown">
                <button class="btn btn-sm btn-ghost-secondary dropdown-toggle" data-bs-toggle="dropdown">
                  <i class="ti ti-dots-vertical"></i>
                </button>
                <div class="dropdown-menu dropdown-menu-end">
                  @if(!$file->is_dir)
                    <a class="dropdown-item" href="#" onclick="fmDownload('{{ addslashes($file->path) }}'); return false;">
                      <i class="ti ti-download me-2"></i>İndir
                    </a>
                  @endif
                  @if($file->editable)
                    <a class="dropdown-item" href="#" onclick="fmEdit('{{ addslashes($file->path) }}'); return false;">
                      <i class="ti ti-pencil me-2"></i>Düzenle
                    </a>
                  @endif
                  <a class="dropdown-item" href="#" onclick="fmRename('{{ addslashes($file->path) }}','{{ addslashes($file->name) }}'); return false;">
                    <i class="ti ti-cursor-text me-2"></i>Yeniden Adlandır
                  </a>
                  <a class="dropdown-item" href="#" onclick="fmCopy('{{ addslashes($file->path) }}'); return false;">
                    <i class="ti ti-copy me-2"></i>Kopyala
                  </a>
                  @if(!$file->is_dir)
                    <a class="dropdown-item" href="#" onclick="fmCompress('{{ addslashes($file->path) }}'); return false;">
                      <i class="ti ti-file-zip me-2"></i>Sıkıştır
                    </a>
                  @endif
                  @if($file->ext === 'zip')
                    <a class="dropdown-item" href="#" onclick="fmExtract('{{ addslashes($file->path) }}'); return false;">
                      <i class="ti ti-file-export me-2"></i>Zip Aç
                    </a>
                  @endif
                  <div class="dropdown-divider"></div>
                  <a class="dropdown-item text-danger" href="#"
                     onclick="fmDelete('{{ addslashes($file->path) }}','{{ $file->is_dir ? '1':'0' }}'); return false;">
                    <i class="ti ti-trash me-2"></i>Sil
                  </a>
                </div>
              </div>
            @endif
          </td>
        </tr>
      @empty
        <tr>
          <td colspan="7" class="text-center text-muted py-5">
            <i class="ti ti-folder-off fs-1 d-block mb-2"></i>
            Bu dizin boş
          </td>
        </tr>
      @endforelse
    </tbody>
  </table>
</div>

<!-- Seçili dosyalar için toplu işlem araç çubuğu -->
<div id="fm-bulk-bar" class="px-3 py-2 border-top d-none">
  <div class="d-flex align-items-center gap-2">
    <span class="text-muted small" id="fm-selected-count">0 seçili</span>
    <button class="btn btn-sm btn-ghost-danger" onclick="fmBulkDelete()">
      <i class="ti ti-trash me-1"></i>Seçilileri Sil
    </button>
  </div>
</div>
