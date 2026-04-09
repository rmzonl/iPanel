<div class="page-wrapper">
  <div class="page-header d-print-none">
    <div class="container-xl">
      <div class="row g-2 align-items-center">
        <div class="col">
          <h2 class="page-title"><i class="ti ti-files me-2"></i>Dosya Yöneticisi</h2>
        </div>
      </div>
    </div>
  </div>

  <div class="page-body">
    <div class="container-xl">
      <div class="card" data-fm-path="{{ $currentPath }}">

        <!-- Araç Çubuğu -->
        <div class="card-header d-flex flex-wrap gap-2 align-items-center">

          <!-- Breadcrumb -->
          <nav aria-label="breadcrumb" class="me-auto">
            <ol class="breadcrumb mb-0">
              @foreach($breadcrumb as $crumb)
                @if($loop->last)
                  <li class="breadcrumb-item active">{{ $crumb['label'] }}</li>
                @else
                  <li class="breadcrumb-item">
                    <a href="#" onclick="fmBrowse('{{ addslashes($crumb['path']) }}'); return false;">
                      {{ $crumb['label'] }}
                    </a>
                  </li>
                @endif
              @endforeach
            </ol>
          </nav>

          <!-- Araçlar -->
          <div class="btn-group btn-group-sm">
            <button class="btn btn-primary" onclick="document.getElementById('fm-upload-input').click()">
              <i class="ti ti-upload me-1"></i> Yükle
            </button>
            <button class="btn btn-outline-secondary" onclick="fmMkdir()">
              <i class="ti ti-folder-plus me-1"></i> Yeni Klasör
            </button>
            <button class="btn btn-outline-secondary" onclick="fmNewFile()">
              <i class="ti ti-file-plus me-1"></i> Yeni Dosya
            </button>
          </div>

          <!-- Gizli yükleme input -->
          <input type="file" id="fm-upload-input" multiple style="display:none"
                 onchange="fmUpload(this.files)">
        </div>

        <!-- Dosya Listesi (Import::usable ile doldurulur) -->
        <div id="fm-list-container">
          @view('FileManager/list')
        </div>

      </div>

      <!-- Yükleme ilerleme çubuğu -->
      <div id="fm-upload-progress" class="mt-2" style="display:none">
        <div class="progress">
          <div class="progress-bar progress-bar-animated" id="fm-progress-bar" style="width:0%"></div>
        </div>
        <small class="text-muted" id="fm-progress-label">Yükleniyor…</small>
      </div>

    </div>
  </div>
</div>

<!-- ── Yeniden Adlandır Modal ── -->
<div class="modal modal-blur fade" id="modal-rename" tabindex="-1">
  <div class="modal-dialog modal-sm">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Yeniden Adlandır</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <input type="text" id="rename-input" class="form-control" placeholder="Yeni isim">
        <input type="hidden" id="rename-path">
      </div>
      <div class="modal-footer">
        <button class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
        <button class="btn btn-primary" onclick="fmRenameSubmit()">Kaydet</button>
      </div>
    </div>
  </div>
</div>

<!-- ── Chmod Modal ── -->
<div class="modal modal-blur fade" id="modal-chmod" tabindex="-1">
  <div class="modal-dialog modal-sm">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">İzin Değiştir (chmod)</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <input type="text" id="chmod-input" class="form-control font-monospace"
               placeholder="755" maxlength="4" pattern="[0-7]{3,4}">
        <input type="hidden" id="chmod-path">
        <small class="text-muted">Örn: 755, 644, 0755</small>
      </div>
      <div class="modal-footer">
        <button class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
        <button class="btn btn-warning" onclick="fmChmodSubmit()">Uygula</button>
      </div>
    </div>
  </div>
</div>

<!-- ── Dizin Oluştur Modal ── -->
<div class="modal modal-blur fade" id="modal-mkdir" tabindex="-1">
  <div class="modal-dialog modal-sm">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Yeni Klasör</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <input type="text" id="mkdir-input" class="form-control" placeholder="Klasör adı">
      </div>
      <div class="modal-footer">
        <button class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
        <button class="btn btn-primary" onclick="fmMkdirSubmit()">Oluştur</button>
      </div>
    </div>
  </div>
</div>

<!-- ── Yeni Dosya Modal ── -->
<div class="modal modal-blur fade" id="modal-newfile" tabindex="-1">
  <div class="modal-dialog modal-sm">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Yeni Dosya</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <input type="text" id="newfile-input" class="form-control" placeholder="dosya.txt">
      </div>
      <div class="modal-footer">
        <button class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
        <button class="btn btn-primary" onclick="fmNewFileSubmit()">Oluştur</button>
      </div>
    </div>
  </div>
</div>

<!-- ── Kopyala Modal ── -->
<div class="modal modal-blur fade" id="modal-copy" tabindex="-1">
  <div class="modal-dialog modal-sm">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Kopyala</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <input type="text" id="copy-dest" class="form-control font-monospace" placeholder="Hedef dizin: /home/user">
        <input type="hidden" id="copy-src">
      </div>
      <div class="modal-footer">
        <button class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
        <button class="btn btn-primary" onclick="fmCopySubmit()">Kopyala</button>
      </div>
    </div>
  </div>
</div>

<!-- ── Editör Modal ── -->
<div class="modal modal-blur fade" id="modal-editor" tabindex="-1">
  <div class="modal-dialog modal-xl modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="editor-title">Dosya Düzenle</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body p-0" id="editor-body">
        <div class="d-flex align-items-center justify-content-center py-5">
          <div class="spinner-border text-muted"></div>
        </div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-secondary" data-bs-dismiss="modal">Kapat</button>
        <button class="btn btn-primary" id="editor-save-btn" onclick="fmEditorSave()">
          <i class="ti ti-device-floppy me-1"></i> Kaydet
        </button>
      </div>
    </div>
  </div>
</div>

<!-- ── CodeMirror ── -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/codemirror.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/theme/dracula.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/codemirror.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/mode/php/php.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/mode/javascript/javascript.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/mode/css/css.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/mode/xml/xml.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/mode/shell/shell.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/mode/sql/sql.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/mode/markdown/markdown.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/addon/edit/matchbrackets.js"></script>
<script src="{{ URL::base('assets/js/filemanager.js') }}"></script>
