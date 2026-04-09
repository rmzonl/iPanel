<div class="border-bottom px-3 py-2 d-flex align-items-center gap-2 bg-dark text-white rounded-top">
  <i class="ti ti-file-code text-azure"></i>
  <span class="font-monospace small">{{ $filePath }}</span>
  <span class="badge bg-azure-lt ms-auto">{{ strtoupper($fileExt) }}</span>
</div>
<div id="fm-editor-wrap" style="height:500px; overflow:auto;">
  <textarea id="fm-codemirror-src" style="display:none;">{{ $fileContent }}</textarea>
</div>

{[
  // Uzantıya göre CodeMirror mode belirle
  $modeMap = ['php'=>'application/x-httpd-php','js'=>'javascript','ts'=>'javascript',
    'css'=>'css','scss'=>'css','html'=>'htmlmixed','htm'=>'htmlmixed',
    'xml'=>'xml','json'=>'application/json','sh'=>'shell','bash'=>'shell',
    'sql'=>'text/x-sql','md'=>'markdown','yaml'=>'yaml','yml'=>'yaml'];
  $cmMode = $modeMap[$fileExt] ?? 'text/plain';
  $cmPath  = htmlspecialchars($filePath, ENT_QUOTES);
]}

{[ echo Script::tag()->compress(function() use ($cmMode, $cmPath) { ?>
(function() {
  var src = document.getElementById('fm-codemirror-src');
  var wrap = document.getElementById('fm-editor-wrap');
  var editor = CodeMirror(wrap, {
    value: src.value,
    mode: '<?= addslashes($cmMode) ?>',
    theme: 'dracula',
    lineNumbers: true,
    matchBrackets: true,
    indentUnit: 2,
    tabSize: 2,
    indentWithTabs: false,
    lineWrapping: false,
    autofocus: true
  });
  editor.setSize('100%', '500px');
  window._fmEditor = editor;
  window._fmEditorPath = '<?= addslashes($cmPath) ?>';
  document.getElementById('editor-title').textContent = '<?= addslashes(basename($cmPath)) ?>';
})();
<?php }); ]}
