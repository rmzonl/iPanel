<div class="page-wrapper">
  <div class="page-header d-print-none">
    <div class="container-xl">
      <div class="row g-2 align-items-center">
        <div class="col">
          <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
              <li class="breadcrumb-item"><a href="{{ URL::base('clients/main') }}">Müşteriler</a></li>
              <li class="breadcrumb-item active">Düzenle</li>
            </ol>
          </nav>
          <h2 class="page-title"><i class="ti ti-edit me-2"></i>Müşteri Düzenle</h2>
        </div>
      </div>
    </div>
  </div>

  <div class="page-body">
    <div class="container-xl">
      <div class="row justify-content-center">
        <div class="col-lg-8">

          @if(!empty($error))
            <div class="alert alert-danger mb-3"><i class="ti ti-alert-circle me-2"></i>{{ $error }}</div>
          @endif

          <form method="POST" action="{{ URL::base('clients/update/' . $client->id) }}" data-ajax="true">
        {[ echo $csrfField ?? ""; ]}
            <div class="card">
              <div class="card-header"><h3 class="card-title">Kişisel Bilgiler</h3></div>
              <div class="card-body">
                <div class="row g-3">
                  <div class="col-md-6">
                    <label class="form-label required">Ad</label>
                    <input type="text" name="first_name" class="form-control" value="{{ $client->first_name }}" required/>
                  </div>
                  <div class="col-md-6">
                    <label class="form-label required">Soyad</label>
                    <input type="text" name="last_name" class="form-control" value="{{ $client->last_name }}" required/>
                  </div>
                  <div class="col-md-6">
                    <label class="form-label required">E-posta</label>
                    <input type="email" name="email" class="form-control" value="{{ $client->email }}" required/>
                  </div>
                  <div class="col-md-6">
                    <label class="form-label">Telefon</label>
                    <input type="text" name="phone" class="form-control" value="{{ $client->phone }}"/>
                  </div>
                  <div class="col-md-12">
                    <label class="form-label">Firma Adı</label>
                    <input type="text" name="company_name" class="form-control" value="{{ $client->company_name }}"/>
                  </div>
                </div>
              </div>
            </div>

            <div class="card mt-3">
              <div class="card-header"><h3 class="card-title">Adres Bilgileri</h3></div>
              <div class="card-body">
                <div class="row g-3">
                  <div class="col-12">
                    <label class="form-label">Adres</label>
                    <textarea name="address" class="form-control" rows="2">{{ $client->address }}</textarea>
                  </div>
                  <div class="col-md-6">
                    <label class="form-label">Şehir</label>
                    <input type="text" name="city" class="form-control" value="{{ $client->city }}"/>
                  </div>
                  <div class="col-md-6">
                    <label class="form-label">Ülke</label>
                    <input type="text" name="country" class="form-control" value="{{ $client->country }}"/>
                  </div>
                </div>
              </div>
            </div>

            <div class="card mt-3">
              <div class="card-header"><h3 class="card-title">Durum & Notlar</h3></div>
              <div class="card-body">
                <div class="row g-3">
                  <div class="col-md-4">
                    <label class="form-label">Durum</label>
                    <select name="status" class="form-select">
                      <option value="active" {[ echo $client->status==='active' ? 'selected' : ''; ]}>Aktif</option>
                      <option value="suspended" {[ echo $client->status==='suspended' ? 'selected' : ''; ]}>Askıya Alındı</option>
                      <option value="terminated" {[ echo $client->status==='terminated' ? 'selected' : ''; ]}>Sonlandırıldı</option>
                    </select>
                  </div>
                  <div class="col-12">
                    <label class="form-label">Notlar</label>
                    <textarea name="notes" class="form-control" rows="3">{{ $client->notes }}</textarea>
                  </div>
                </div>
              </div>
              <div class="card-footer d-flex justify-content-end gap-2">
                <a href="{{ URL::base('clients/main') }}" class="btn btn-ghost-secondary">İptal</a>
                <button type="submit" class="btn btn-primary">
                  <i class="ti ti-device-floppy me-1"></i> Güncelle
                </button>
              </div>
            </div>
          </form>

        </div>
      </div>
    </div>
  </div>
</div>
