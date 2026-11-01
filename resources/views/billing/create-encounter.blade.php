@extends('layouts.app')
@section('title', 'Invoice — ' . $encounter->encounter_number)
@section('breadcrumb')
  <li><a href="{{ route('dashboard') }}" style="color:var(--clr-green);">Dashboard</a></li>
  <li style="color:var(--clr-grey-400);">/</li>
  <li><a href="{{ route('encounters.show', $encounter) }}" style="color:var(--clr-green);">{{ $encounter->encounter_number }}</a></li>
  <li style="color:var(--clr-grey-400);">/</li>
  <li style="font-weight:500;">Create Invoice</li>
@endsection

@section('content')
<div class="hmis-page-header">
  <div>
    <h1 class="hmis-page-header__title">Create Invoice</h1>
    <p class="hmis-page-header__subtitle">{{ $encounter->patient->full_name }} — {{ $encounter->encounter_number }}</p>
  </div>
</div>

<form method="POST" action="{{ route('billing.store') }}" novalidate
      x-data="{
        items: [{ description: '', item_type: 'consultation', stock_item_id: '', quantity: 1, unit_price: 0, discount_percent: 0 }],
        addItem() { this.items.push({ description: '', item_type: 'service', stock_item_id: '', quantity: 1, unit_price: 0, discount_percent: 0 }); },
        removeItem(i) { if(this.items.length > 1) this.items.splice(i, 1); },
        lineTotal(item) { return (item.unit_price * item.quantity * (1 - item.discount_percent/100)).toFixed(2); },
        grandTotal() { return this.items.reduce((s,i) => s + parseFloat(this.lineTotal(i)), 0).toFixed(2); }
      }">
  @csrf
  <input type="hidden" name="patient_id" value="{{ $encounter->patient_id }}">
  <input type="hidden" name="encounter_id" value="{{ $encounter->id }}">

  <div style="display:grid;grid-template-columns:2fr 1fr;gap:var(--space-5);">
    <div>
      <div class="hmis-card">
        <div class="hmis-card__header">
          <h2 class="hmis-card__title">Invoice Items</h2>
          <button type="button" @click="addItem()" class="btn-hmis-secondary" style="padding:4px 12px;font-size:var(--font-size-xs);">
            <i class="bi bi-plus"></i> Add Item
          </button>
        </div>
        <div class="hmis-card__body">
          <template x-for="(item, index) in items" :key="index">
            <div style="border:1px solid var(--border-color);border-radius:var(--radius-md);padding:var(--space-4);margin-bottom:var(--space-3);">
              <div style="display:grid;grid-template-columns:2fr 1fr;gap:var(--space-3);margin-bottom:var(--space-3);">
                <div class="hmis-form-group" style="margin:0;">
                  <label class="hmis-label">Description</label>
                  <input type="text" :name="`items[${index}][description]`" x-model="item.description" class="hmis-input" required>
                </div>
                <div class="hmis-form-group" style="margin:0;">
                  <label class="hmis-label">Type</label>
                  <select :name="`items[${index}][item_type]`" x-model="item.item_type" class="hmis-select">
                    <option value="consultation">Consultation</option>
                    <option value="optical">Optical</option>
                    <option value="drug">Drug</option>
                    <option value="procedure">Procedure</option>
                    <option value="service">Service</option>
                  </select>
                </div>
              </div>
              <div style="display:grid;grid-template-columns:1fr 1fr 1fr auto;gap:var(--space-3);align-items:flex-end;">
                <div class="hmis-form-group" style="margin:0;">
                  <label class="hmis-label">Stock Item</label>
                  <select :name="`items[${index}][stock_item_id]`" x-model="item.stock_item_id" class="hmis-select"
                          @change="const s=$event.target.options[$event.target.selectedIndex]; if(s.dataset.price){item.unit_price=s.dataset.price;}">
                    <option value="">— None —</option>
                    @foreach($stockItems as $si)
                      <option value="{{ $si->id }}" data-price="{{ $si->unit_price }}">{{ $si->name }}</option>
                    @endforeach
                  </select>
                </div>
                <div class="hmis-form-group" style="margin:0;">
                  <label class="hmis-label">Qty</label>
                  <input type="number" :name="`items[${index}][quantity]`" x-model.number="item.quantity" class="hmis-input" min="1" required>
                </div>
                <div class="hmis-form-group" style="margin:0;">
                  <label class="hmis-label">Unit Price (KSh)</label>
                  <input type="number" step="0.01" :name="`items[${index}][unit_price]`" x-model.number="item.unit_price" class="hmis-input" min="0" required>
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

    <div>
      <div class="hmis-card" style="position:sticky;top:calc(var(--topbar-height) + var(--space-4));">
        <div class="hmis-card__header"><h2 class="hmis-card__title">Summary</h2></div>
        <div class="hmis-card__body">
          <div style="font-size:var(--font-size-2xl);font-weight:700;margin-bottom:var(--space-4);">
            KSh <span x-text="grandTotal()">0.00</span>
          </div>
          <button type="submit" class="btn-hmis-primary" style="width:100%;justify-content:center;padding:var(--space-3);">
            <i class="bi bi-receipt"></i> Issue Invoice
          </button>
          <a href="{{ route('encounters.show', $encounter) }}" class="btn-hmis-secondary" style="width:100%;justify-content:center;margin-top:var(--space-3);">
            <i class="bi bi-x"></i> Cancel
          </a>
        </div>
      </div>
    </div>
  </div>
</form>
@endsection