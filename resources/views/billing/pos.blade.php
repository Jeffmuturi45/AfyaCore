@extends('layouts.app')
@section('title', 'POS — Walk-in Sale')
@section('breadcrumb')
  <li><a href="{{ route('dashboard') }}" style="color:var(--clr-green);">Dashboard</a></li>
  <li style="color:var(--clr-grey-400);">/</li>
  <li><a href="{{ route('billing.index') }}" style="color:var(--clr-green);">Billing</a></li>
  <li style="color:var(--clr-grey-400);">/</li>
  <li style="font-weight:500;">POS</li>
@endsection

@section('content')
<div class="hmis-page-header">
  <h1 class="hmis-page-header__title"><i class="bi bi-cart-fill text-green"></i> Point of Sale</h1>
</div>

<form method="POST" action="{{ route('billing.store') }}" novalidate
      x-data="{
        items: [{ description: '', item_type: 'optical', stock_item_id: '', quantity: 1, unit_price: 0, discount_percent: 0 }],
        addItem() { this.items.push({ description: '', item_type: 'optical', stock_item_id: '', quantity: 1, unit_price: 0, discount_percent: 0 }); },
        removeItem(i) { if(this.items.length > 1) this.items.splice(i, 1); },
        lineTotal(item) { return (item.unit_price * item.quantity * (1 - item.discount_percent/100)).toFixed(2); },
        grandTotal() { return this.items.reduce((s,i) => s + parseFloat(this.lineTotal(i)), 0).toFixed(2); }
      }">
  @csrf

  <div style="display:grid;grid-template-columns:2fr 1fr;gap:var(--space-5);">
    <div>
      {{-- Patient --}}
      <div class="hmis-card" style="margin-bottom:var(--space-4);">
        <div class="hmis-card__header"><h2 class="hmis-card__title">Patient</h2></div>
        <div class="hmis-card__body">
          <div class="hmis-form-group">
            <label class="hmis-label">Select Patient <span class="required">*</span></label>
            <select name="patient_id" class="hmis-select" required>
              <option value="">— Select patient —</option>
              @foreach($patients as $p)
                <option value="{{ $p->id }}">{{ $p->full_name }} — {{ $p->phone_primary }}</option>
              @endforeach
            </select>
            @error('patient_id')<span class="hmis-error">{{ $message }}</span>@enderror
          </div>
        </div>
      </div>

      {{-- Items --}}
      <div class="hmis-card">
        <div class="hmis-card__header">
          <h2 class="hmis-card__title">Items</h2>
          <button type="button" @click="addItem()" class="btn-hmis-secondary" style="padding:4px 12px;font-size:var(--font-size-xs);">
            <i class="bi bi-plus"></i> Add Item
          </button>
        </div>
        <div class="hmis-card__body">
          <template x-for="(item, index) in items" :key="index">
            <div style="border:1px solid var(--border-color);border-radius:var(--radius-md);padding:var(--space-4);margin-bottom:var(--space-3);">
              <div style="display:grid;grid-template-columns:2fr 1fr 1fr;gap:var(--space-3);margin-bottom:var(--space-3);">
                <div class="hmis-form-group" style="margin:0;">
                  <label class="hmis-label">Description</label>
                  <input type="text" :name="`items[${index}][description]`" x-model="item.description"
                         class="hmis-input" required>
                </div>
                <div class="hmis-form-group" style="margin:0;">
                  <label class="hmis-label">Type</label>
                  <select :name="`items[${index}][item_type]`" x-model="item.item_type" class="hmis-select">
                    <option value="optical">Optical</option>
                    <option value="drug">Drug</option>
                    <option value="service">Service</option>
                    <option value="consultation">Consultation</option>
                    <option value="procedure">Procedure</option>
                  </select>
                </div>
                <div class="hmis-form-group" style="margin:0;">
                  <label class="hmis-label">Stock Item</label>
                  <select :name="`items[${index}][stock_item_id]`" x-model="item.stock_item_id" class="hmis-select"
                          @change="const s = $event.target.options[$event.target.selectedIndex]; if(s.dataset.price) { item.unit_price = s.dataset.price; item.description = s.text.split(' (')[0]; }">
                    <option value="">— Optional —</option>
                    @foreach($stockItems as $si)
                      <option value="{{ $si->id }}" data-price="{{ $si->unit_price }}">
                        {{ $si->name }} (KSh {{ number_format($si->unit_price, 2) }})
                      </option>
                    @endforeach
                  </select>
                </div>
              </div>
              <div style="display:grid;grid-template-columns:1fr 1fr 1fr auto;gap:var(--space-3);align-items:flex-end;">
                <div class="hmis-form-group" style="margin:0;">
                  <label class="hmis-label">Qty</label>
                  <input type="number" :name="`items[${index}][quantity]`" x-model.number="item.quantity"
                         class="hmis-input" min="1" required>
                </div>
                <div class="hmis-form-group" style="margin:0;">
                  <label class="hmis-label">Unit Price (KSh)</label>
                  <input type="number" step="0.01" :name="`items[${index}][unit_price]`" x-model.number="item.unit_price"
                         class="hmis-input" min="0" required>
                </div>
                <div class="hmis-form-group" style="margin:0;">
                  <label class="hmis-label">Discount %</label>
                  <input type="number" step="0.1" :name="`items[${index}][discount_percent]`" x-model.number="item.discount_percent"
                         class="hmis-input" min="0" max="100">
                </div>
                <button type="button" @click="removeItem(index)" class="btn-hmis-danger" style="padding:8px 10px;" x-show="items.length > 1">
                  <i class="bi bi-trash"></i>
                </button>
              </div>
              <div style="text-align:right;margin-top:var(--space-2);font-size:var(--font-size-sm);color:var(--clr-grey-600);">
                Line total: <strong>KSh <span x-text="lineTotal(item)"></span></strong>
              </div>
            </div>
          </template>
        </div>
      </div>
    </div>

    {{-- Summary --}}
    <div>
      <div class="hmis-card" style="position:sticky;top:calc(var(--topbar-height) + var(--space-4));">
        <div class="hmis-card__header"><h2 class="hmis-card__title">Order Summary</h2></div>
        <div class="hmis-card__body">
          <div style="font-size:var(--font-size-2xl);font-weight:700;color:var(--clr-charcoal);margin-bottom:var(--space-4);">
            KSh <span x-text="grandTotal()">0.00</span>
          </div>
          <div class="hmis-alert hmis-alert--info" style="margin-bottom:var(--space-4);">
            <i class="bi bi-info-circle-fill"></i>
            <div style="font-size:var(--font-size-xs);">Payment is collected after the invoice is created.</div>
          </div>
          <button type="submit" class="btn-hmis-primary" style="width:100%;justify-content:center;padding:var(--space-3);">
            <i class="bi bi-receipt"></i> Create Invoice
          </button>
          <a href="{{ route('billing.index') }}" class="btn-hmis-secondary" style="width:100%;justify-content:center;margin-top:var(--space-3);">
            <i class="bi bi-x"></i> Cancel
          </a>
        </div>
      </div>
    </div>
  </div>
</form>
@endsection