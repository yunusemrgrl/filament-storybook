<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\ComponentSurface;
use App\InvoiceStatus;
use App\Models\ComponentDefinition;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Page;
use App\Models\Payment;
use App\Models\Product;
use App\PageStatus;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class StarterInvoicingSeeder extends Seeder
{
    public function run(): void
    {
        $customers = Customer::factory()
            ->count(5)
            ->create();

        $products = Product::factory()
            ->count(8)
            ->create();

        $draftInvoice = $this->createInvoice($customers->firstOrFail(), $products->take(2), InvoiceStatus::Draft, 'INV-100001');
        $sentInvoice = $this->createInvoice($customers->skip(1)->firstOrFail(), $products->slice(2, 2), InvoiceStatus::Sent, 'INV-100002');
        $paidInvoice = $this->createInvoice($customers->skip(2)->firstOrFail(), $products->slice(4, 2), InvoiceStatus::Paid, 'INV-100003');
        $archivedInvoice = $this->createInvoice($customers->skip(3)->firstOrFail(), $products->slice(6, 2), InvoiceStatus::Archived, 'INV-100004');

        Payment::query()->create([
            'invoice_id' => $paidInvoice->getKey(),
            'amount_cents' => $paidInvoice->total_cents,
            'currency' => $paidInvoice->currency,
            'method' => 'bank_transfer',
            'reference' => 'PAY-100003',
            'paid_at' => now()->subDay(),
            'notes' => 'Seeded settlement payment.',
        ]);
        Payment::query()->create([
            'invoice_id' => $archivedInvoice->getKey(),
            'amount_cents' => $archivedInvoice->total_cents,
            'currency' => $archivedInvoice->currency,
            'method' => 'credit_card',
            'reference' => 'PAY-100004',
            'paid_at' => now()->subDays(2),
            'notes' => 'Seeded archived invoice payment.',
        ]);

        $this->seedInvoiceManagementWorkspace();
    }

    /**
     * @param  Collection<int, Product>  $products
     */
    private function createInvoice(Customer $customer, Collection $products, InvoiceStatus $status, string $invoiceNumber): Invoice
    {
        $invoice = Invoice::query()->create([
            'customer_id' => $customer->getKey(),
            'invoice_number' => $invoiceNumber,
            'status' => $status,
            'issued_at' => now()->subDays(5),
            'due_at' => now()->addDays(14),
            'sent_at' => in_array($status, [InvoiceStatus::Sent, InvoiceStatus::Paid, InvoiceStatus::Cancelled, InvoiceStatus::Archived], true)
                ? now()->subDays(3)
                : null,
            'paid_at' => in_array($status, [InvoiceStatus::Paid, InvoiceStatus::Archived], true)
                ? now()->subDay()
                : null,
            'archived_at' => $status === InvoiceStatus::Archived ? now()->subHours(6) : null,
            'subtotal_cents' => 0,
            'tax_total_cents' => 0,
            'total_cents' => 0,
            'currency' => 'USD',
            'notes' => 'Starter invoicing workspace seed.',
        ]);

        $subtotal = 0;

        foreach ($products->values() as $index => $product) {
            $quantity = $index + 1;
            $lineTotal = $quantity * $product->unit_price_cents;
            $subtotal += $lineTotal;

            $invoice->invoiceItems()->create([
                'product_id' => $product->getKey(),
                'description' => $product->name,
                'quantity' => $quantity,
                'unit_price_cents' => $product->unit_price_cents,
                'line_total_cents' => $lineTotal,
                'position' => $index + 1,
            ]);
        }

        $taxTotal = (int) round($subtotal * 0.2);

        $invoice->update([
            'subtotal_cents' => $subtotal,
            'tax_total_cents' => $taxTotal,
            'total_cents' => $subtotal + $taxTotal,
        ]);

        return $invoice->refresh();
    }

    private function seedInvoiceManagementWorkspace(): void
    {
        $grid = $this->component('filament.layout.grid');
        $section = $this->component('filament.layout.section');
        $textInput = $this->component('filament.form.text_input');
        $select = $this->component('filament.form.select');
        $money = $this->component('filament.form.money');
        $dateTime = $this->component('filament.form.date_time');
        $repeater = $this->component('filament.form.repeater');
        $textColumn = $this->component('filament.table.text_column');
        $badgeColumn = $this->component('filament.table.badge_column');
        $tableWidget = $this->component('filament.widget.table_widget');
        $actionButton = $this->component('filament.action.button');

        Page::query()->updateOrCreate(
            ['slug' => 'manage-invoices'],
            [
                'title' => 'Manage Invoices',
                'status' => PageStatus::Published,
                'blocks' => [
                    $this->node($grid, [
                        'columns' => 2,
                        'description' => 'Invoice management workspace grid.',
                    ], [
                        $this->node($section, [
                            'heading' => 'Invoice registry',
                            'description' => 'Live invoice table compiled from the Struktura DSL.',
                        ], [
                            $this->node($tableWidget, [
                                'data_source_type' => 'model',
                                'payload_path' => 'widgets.invoice_registry',
                                'data_source_model' => Invoice::class,
                                'relationship' => '',
                                'relationship_type' => '',
                                'display_column' => '',
                                'value_column' => '',
                                'hydration_logic' => 'table.widget',
                                'widget_key' => 'invoice_registry',
                                'query_scope' => 'latest()',
                                'pagination_size' => 25,
                            ], [
                                $this->node($textColumn, [
                                    'data_source_type' => 'model',
                                    'column_path' => 'invoice_number',
                                    'data_source_model' => Invoice::class,
                                    'relationship' => '',
                                    'relationship_type' => '',
                                    'display_column' => 'invoice_number',
                                    'value_column' => 'id',
                                    'hydration_logic' => 'table.column',
                                    'label' => 'Invoice #',
                                    'is_searchable' => true,
                                    'is_sortable' => true,
                                    'actions' => [],
                                ]),
                                $this->node($textColumn, [
                                    'data_source_type' => 'relationship',
                                    'column_path' => 'customer.name',
                                    'data_source_model' => Invoice::class,
                                    'relationship' => 'customer',
                                    'relationship_type' => 'belongsTo',
                                    'display_column' => 'name',
                                    'value_column' => 'id',
                                    'hydration_logic' => 'table.column',
                                    'label' => 'Customer',
                                    'is_searchable' => true,
                                    'is_sortable' => true,
                                    'actions' => [],
                                ]),
                                $this->node($badgeColumn, [
                                    'data_source_type' => 'model',
                                    'column_path' => 'status',
                                    'data_source_model' => Invoice::class,
                                    'relationship' => '',
                                    'relationship_type' => '',
                                    'display_column' => 'invoice_number',
                                    'value_column' => 'id',
                                    'hydration_logic' => 'table.column',
                                    'label' => 'Status',
                                    'is_searchable' => true,
                                    'is_sortable' => true,
                                    'state_colors' => [
                                        ['state' => 'draft', 'color' => 'gray'],
                                        ['state' => 'sent', 'color' => 'warning'],
                                        ['state' => 'paid', 'color' => 'success'],
                                        ['state' => 'cancelled', 'color' => 'danger'],
                                        ['state' => 'archived', 'color' => 'gray'],
                                    ],
                                    'actions' => [],
                                ]),
                                $this->node($textColumn, [
                                    'data_source_type' => 'model',
                                    'column_path' => 'total_cents',
                                    'data_source_model' => Invoice::class,
                                    'relationship' => '',
                                    'relationship_type' => '',
                                    'display_column' => 'invoice_number',
                                    'value_column' => 'id',
                                    'hydration_logic' => 'table.column',
                                    'label' => 'Total (minor units)',
                                    'is_searchable' => false,
                                    'is_sortable' => true,
                                    'actions' => [],
                                ]),
                                $this->node($actionButton, [
                                    'action_name' => 'send_invoice',
                                    'label' => 'Send Invoice',
                                    'trigger_style' => 'button',
                                    'color' => 'primary',
                                    'handler' => 'App\\StarterKits\\StrukturaEngine\\Actions\\SendInvoice',
                                    'event' => 'invoice.send',
                                    'transition_from' => 'draft',
                                    'transition_to' => 'sent',
                                    'guard' => 'invoiceCanBeSent',
                                    'requires_confirmation' => true,
                                ]),
                                $this->node($actionButton, [
                                    'action_name' => 'record_payment',
                                    'label' => 'Record Payment',
                                    'trigger_style' => 'slide_over',
                                    'color' => 'success',
                                    'handler' => 'App\\StarterKits\\StrukturaEngine\\Actions\\RecordPayment',
                                    'event' => 'invoice.record-payment',
                                    'transition_from' => 'sent',
                                    'transition_to' => 'paid',
                                    'guard' => 'invoiceCanAcceptPayment',
                                    'requires_confirmation' => false,
                                ], [
                                    $this->node($grid, [
                                        'columns' => 2,
                                        'description' => 'Payment capture modal schema.',
                                    ], [
                                        $this->node($money, [
                                            'data_source_type' => 'state',
                                            'payload_path' => 'amount_cents',
                                            'data_source_model' => Payment::class,
                                            'relationship' => '',
                                            'relationship_type' => '',
                                            'display_column' => '',
                                            'value_column' => '',
                                            'hydration_logic' => 'state',
                                            'label' => 'Payment amount',
                                            'placeholder' => '120.00',
                                            'helper_text' => 'Captured in minor units and hydrated as money.',
                                            'is_required' => true,
                                            'validation_rules' => 'required|integer|min:1',
                                            'currency' => 'USD',
                                            'locale' => 'en_US',
                                            'decimals' => 2,
                                            'prefix' => '$',
                                            'actions' => [],
                                        ]),
                                        $this->node($dateTime, [
                                            'data_source_type' => 'state',
                                            'payload_path' => 'paid_at',
                                            'data_source_model' => Payment::class,
                                            'relationship' => '',
                                            'relationship_type' => '',
                                            'display_column' => '',
                                            'value_column' => '',
                                            'hydration_logic' => 'state',
                                            'label' => 'Paid at',
                                            'helper_text' => 'Payment timestamp captured by the workflow modal.',
                                            'is_required' => true,
                                            'validation_rules' => 'required|date',
                                            'format' => 'Y-m-d H:i',
                                            'seconds' => false,
                                            'timezone' => 'UTC',
                                            'min_date' => '',
                                            'max_date' => '',
                                            'actions' => [],
                                        ]),
                                        $this->node($select, [
                                            'data_source_type' => 'state',
                                            'payload_path' => 'method',
                                            'data_source_model' => Payment::class,
                                            'relationship' => '',
                                            'relationship_type' => '',
                                            'display_column' => '',
                                            'value_column' => '',
                                            'hydration_logic' => 'state',
                                            'label' => 'Payment method',
                                            'is_required' => true,
                                            'is_searchable' => false,
                                            'is_multiple' => false,
                                            'validation_rules' => 'required|in:bank_transfer,credit_card,cash',
                                            'options' => [
                                                ['value' => 'bank_transfer', 'label' => 'Bank transfer'],
                                                ['value' => 'credit_card', 'label' => 'Credit card'],
                                                ['value' => 'cash', 'label' => 'Cash'],
                                            ],
                                            'actions' => [],
                                        ]),
                                        $this->node($textInput, [
                                            'data_source_type' => 'state',
                                            'payload_path' => 'notes',
                                            'data_source_model' => Payment::class,
                                            'relationship' => '',
                                            'relationship_type' => '',
                                            'display_column' => '',
                                            'value_column' => '',
                                            'hydration_logic' => 'state',
                                            'label' => 'Payment notes',
                                            'placeholder' => 'Remittance reference',
                                            'helper_text' => 'Optional payment memo stored alongside the record.',
                                            'is_required' => false,
                                            'validation_rules' => 'nullable|string|max:255',
                                            'min_length' => null,
                                            'max_length' => 255,
                                            'is_searchable' => false,
                                            'input_mode' => 'text',
                                            'actions' => [],
                                        ]),
                                    ]),
                                ]),
                                $this->node($actionButton, [
                                    'action_name' => 'cancel_invoice',
                                    'label' => 'Cancel Invoice',
                                    'trigger_style' => 'slide_over',
                                    'color' => 'danger',
                                    'handler' => 'App\\StarterKits\\StrukturaEngine\\Actions\\CancelInvoice',
                                    'event' => 'invoice.cancel',
                                    'transition_from' => 'draft',
                                    'transition_to' => 'cancelled',
                                    'guard' => 'invoiceCanBeCancelled',
                                    'requires_confirmation' => false,
                                ], [
                                    $this->node($textInput, [
                                        'data_source_type' => 'state',
                                        'payload_path' => 'reason',
                                        'data_source_model' => Invoice::class,
                                        'relationship' => '',
                                        'relationship_type' => '',
                                        'display_column' => '',
                                        'value_column' => '',
                                        'hydration_logic' => 'state',
                                        'label' => 'Cancellation reason',
                                        'placeholder' => 'Optional internal reason',
                                        'helper_text' => 'Stored in the workflow audit payload when provided.',
                                        'is_required' => false,
                                        'validation_rules' => 'nullable|string|max:255',
                                        'min_length' => null,
                                        'max_length' => 255,
                                        'is_searchable' => false,
                                        'input_mode' => 'text',
                                        'actions' => [],
                                    ]),
                                ]),
                                $this->node($actionButton, [
                                    'action_name' => 'archive_invoice',
                                    'label' => 'Archive Invoice',
                                    'trigger_style' => 'button',
                                    'color' => 'secondary',
                                    'handler' => 'App\\StarterKits\\StrukturaEngine\\Actions\\ArchiveInvoice',
                                    'event' => 'invoice.archive',
                                    'transition_from' => 'paid',
                                    'transition_to' => 'archived',
                                    'guard' => 'invoiceCanBeArchived',
                                    'requires_confirmation' => true,
                                ]),
                            ]),
                        ]),
                        $this->node($section, [
                            'heading' => 'Invoice editor',
                            'description' => 'Nested form schema for invoice authoring and workflow transitions.',
                        ], [
                            $this->node($grid, [
                                'columns' => 2,
                                'description' => 'Invoice form metadata grid.',
                            ], [
                                $this->node($select, [
                                    'data_source_type' => 'relationship',
                                    'payload_path' => 'form.customer_id',
                                    'data_source_model' => Invoice::class,
                                    'relationship' => 'customer',
                                    'relationship_type' => 'belongsTo',
                                    'display_column' => 'name',
                                    'value_column' => 'id',
                                    'hydration_logic' => 'relationship',
                                    'label' => 'Customer',
                                    'is_required' => true,
                                    'is_searchable' => true,
                                    'is_multiple' => false,
                                    'validation_rules' => 'required|exists:customers,id',
                                    'options' => [],
                                    'actions' => [],
                                ]),
                                $this->node($textInput, [
                                    'data_source_type' => 'state',
                                    'payload_path' => 'form.invoice_number',
                                    'data_source_model' => Invoice::class,
                                    'relationship' => '',
                                    'relationship_type' => '',
                                    'display_column' => 'invoice_number',
                                    'value_column' => 'id',
                                    'hydration_logic' => 'state',
                                    'label' => 'Invoice number',
                                    'placeholder' => 'INV-100004',
                                    'helper_text' => 'Primary business identifier.',
                                    'is_required' => true,
                                    'validation_rules' => 'required|string|max:255',
                                    'min_length' => 3,
                                    'max_length' => 255,
                                    'is_searchable' => false,
                                    'input_mode' => 'text',
                                    'actions' => [],
                                ]),
                                $this->node($select, [
                                    'data_source_type' => 'state',
                                    'payload_path' => 'form.status',
                                    'data_source_model' => Invoice::class,
                                    'relationship' => '',
                                    'relationship_type' => '',
                                    'display_column' => 'invoice_number',
                                    'value_column' => 'id',
                                    'hydration_logic' => 'state',
                                    'label' => 'Status',
                                    'is_required' => true,
                                    'is_searchable' => false,
                                    'is_multiple' => false,
                                    'validation_rules' => 'required|in:draft,sent,paid,cancelled,archived',
                                    'options' => [
                                        ['value' => 'draft', 'label' => 'Draft'],
                                        ['value' => 'sent', 'label' => 'Sent'],
                                        ['value' => 'paid', 'label' => 'Paid'],
                                        ['value' => 'cancelled', 'label' => 'Cancelled'],
                                        ['value' => 'archived', 'label' => 'Archived'],
                                    ],
                                    'actions' => [],
                                ]),
                                $this->node($textInput, [
                                    'data_source_type' => 'state',
                                    'payload_path' => 'form.notes',
                                    'data_source_model' => Invoice::class,
                                    'relationship' => '',
                                    'relationship_type' => '',
                                    'display_column' => 'invoice_number',
                                    'value_column' => 'id',
                                    'hydration_logic' => 'state',
                                    'label' => 'Notes',
                                    'placeholder' => 'Internal workflow notes',
                                    'helper_text' => 'Technical note field.',
                                    'is_required' => false,
                                    'validation_rules' => 'nullable|string|max:255',
                                    'min_length' => null,
                                    'max_length' => 255,
                                    'is_searchable' => false,
                                    'input_mode' => 'text',
                                    'actions' => [],
                                ]),
                                $this->node($dateTime, [
                                    'data_source_type' => 'state',
                                    'payload_path' => 'form.issued_at',
                                    'data_source_model' => Invoice::class,
                                    'relationship' => '',
                                    'relationship_type' => '',
                                    'display_column' => '',
                                    'value_column' => '',
                                    'hydration_logic' => 'state',
                                    'label' => 'Issued at',
                                    'helper_text' => 'Invoice issue timestamp.',
                                    'is_required' => true,
                                    'validation_rules' => 'required|date',
                                    'format' => 'Y-m-d H:i',
                                    'seconds' => false,
                                    'timezone' => 'UTC',
                                    'min_date' => '',
                                    'max_date' => '',
                                    'actions' => [],
                                ]),
                                $this->node($dateTime, [
                                    'data_source_type' => 'state',
                                    'payload_path' => 'form.due_at',
                                    'data_source_model' => Invoice::class,
                                    'relationship' => '',
                                    'relationship_type' => '',
                                    'display_column' => '',
                                    'value_column' => '',
                                    'hydration_logic' => 'state',
                                    'label' => 'Due at',
                                    'helper_text' => 'Invoice payment due timestamp.',
                                    'is_required' => true,
                                    'validation_rules' => 'required|date|after_or_equal:issued_at',
                                    'format' => 'Y-m-d H:i',
                                    'seconds' => false,
                                    'timezone' => 'UTC',
                                    'min_date' => '',
                                    'max_date' => '',
                                    'actions' => [],
                                ]),
                            ]),
                            $this->node($repeater, [
                                'data_source_type' => 'relationship',
                                'payload_path' => 'form.invoice_items',
                                'data_source_model' => Invoice::class,
                                'relationship' => 'invoiceItems',
                                'relationship_type' => 'hasMany',
                                'display_column' => 'description',
                                'value_column' => 'id',
                                'hydration_logic' => 'relationship',
                                'label' => 'Line items',
                                'item_label_path' => 'description',
                                'validation_rules' => 'required|array|min:1',
                                'actions' => [],
                            ], [
                                $this->node($select, [
                                    'data_source_type' => 'relationship',
                                    'payload_path' => 'product_id',
                                    'data_source_model' => InvoiceItem::class,
                                    'relationship' => 'product',
                                    'relationship_type' => 'belongsTo',
                                    'display_column' => 'name',
                                    'value_column' => 'id',
                                    'hydration_logic' => 'relationship',
                                    'label' => 'Product',
                                    'is_required' => true,
                                    'is_searchable' => true,
                                    'is_multiple' => false,
                                    'validation_rules' => 'required|exists:products,id',
                                    'options' => [],
                                    'actions' => [],
                                ]),
                                $this->node($textInput, [
                                    'data_source_type' => 'relationship',
                                    'payload_path' => 'description',
                                    'data_source_model' => InvoiceItem::class,
                                    'relationship' => 'product',
                                    'relationship_type' => 'belongsTo',
                                    'display_column' => 'description',
                                    'value_column' => 'id',
                                    'hydration_logic' => 'relationship',
                                    'label' => 'Description',
                                    'placeholder' => 'Hydrated from selected product',
                                    'helper_text' => 'Compiler should derive this from Product.description.',
                                    'is_required' => true,
                                    'validation_rules' => 'required|string|max:255',
                                    'min_length' => null,
                                    'max_length' => 255,
                                    'is_searchable' => false,
                                    'input_mode' => 'text',
                                    'actions' => [],
                                ]),
                                $this->node($textInput, [
                                    'data_source_type' => 'state',
                                    'payload_path' => 'quantity',
                                    'data_source_model' => InvoiceItem::class,
                                    'relationship' => '',
                                    'relationship_type' => '',
                                    'display_column' => 'description',
                                    'value_column' => 'id',
                                    'hydration_logic' => 'state',
                                    'label' => 'Quantity',
                                    'placeholder' => '1',
                                    'helper_text' => 'Integer quantity per line item.',
                                    'is_required' => true,
                                    'validation_rules' => 'required|integer|min:1',
                                    'min_length' => 1,
                                    'max_length' => 4,
                                    'is_searchable' => false,
                                    'input_mode' => 'numeric',
                                    'actions' => [],
                                ]),
                                $this->node($money, [
                                    'data_source_type' => 'relationship',
                                    'payload_path' => 'unit_price_cents',
                                    'data_source_model' => InvoiceItem::class,
                                    'relationship' => 'product',
                                    'relationship_type' => 'belongsTo',
                                    'display_column' => 'unit_price_cents',
                                    'value_column' => 'id',
                                    'hydration_logic' => 'relationship',
                                    'label' => 'Unit price',
                                    'placeholder' => '250.00',
                                    'helper_text' => 'Hydrated from Product.unit_price_cents and formatted as money.',
                                    'is_required' => true,
                                    'validation_rules' => 'required|integer|min:0',
                                    'currency' => 'USD',
                                    'locale' => 'en_US',
                                    'decimals' => 2,
                                    'prefix' => '$',
                                    'actions' => [],
                                ]),
                                $this->node($money, [
                                    'data_source_type' => 'state',
                                    'payload_path' => 'line_total_cents',
                                    'data_source_model' => InvoiceItem::class,
                                    'relationship' => '',
                                    'relationship_type' => '',
                                    'display_column' => 'description',
                                    'value_column' => 'id',
                                    'hydration_logic' => 'computed',
                                    'label' => 'Line total',
                                    'placeholder' => '500.00',
                                    'helper_text' => 'Computed at runtime from quantity * unit_price_cents.',
                                    'is_required' => true,
                                    'validation_rules' => 'required|integer|min:0',
                                    'currency' => 'USD',
                                    'locale' => 'en_US',
                                    'decimals' => 2,
                                    'prefix' => '$',
                                    'actions' => [],
                                ], computedLogic: [
                                    'type' => 'formula',
                                    'expression' => '{quantity} * {unit_price_cents}',
                                    'precision' => 2,
                                ]),
                            ]),
                        ]),
                    ]),
                ],
            ],
        );
    }

    private function component(string $handle): ComponentDefinition
    {
        return ComponentDefinition::query()
            ->forSurface(ComponentSurface::Page)
            ->where('handle', $handle)
            ->firstOrFail();
    }

    /**
     * @param  array<string, mixed>  $props
     * @param  array<int, array<string, mixed>>  $children
     * @param  array<string, mixed>  $computedLogic
     * @return array<string, mixed>
     */
    private function node(ComponentDefinition $definition, array $props = [], array $children = [], array $computedLogic = []): array
    {
        return [
            'id' => (string) Str::uuid(),
            'type' => $definition->getBlockType(),
            'surface' => $definition->getSurface()->value,
            'label' => $this->nodeLabel($definition, $props),
            'props' => $definition->normalizeProps(array_merge($definition->getDefaultValues(), $props)),
            'children' => $children,
            'computed_logic' => $computedLogic,
            'meta' => [
                'slug' => $definition->handle,
                'description' => $definition->description,
                'group' => $definition->category,
                'view' => $definition->view,
                'source' => 'definition',
                'variant' => 'default',
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $props
     */
    private function nodeLabel(ComponentDefinition $definition, array $props): string
    {
        foreach (['label', 'heading', 'widget_key', 'action_name'] as $candidate) {
            $value = $props[$candidate] ?? null;

            if (is_string($value) && trim($value) !== '') {
                return trim($value);
            }
        }

        return $definition->name;
    }
}
