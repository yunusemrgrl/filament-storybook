<?php

use App\ComponentSurface;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Page;
use App\Models\Payment;
use App\Models\Product;
use App\Models\User;
use App\StarterKits\StrukturaEngine\Actions\ArchiveInvoice;
use App\StarterKits\StrukturaEngine\Actions\CancelInvoice;
use App\StarterKits\StrukturaEngine\Actions\Guards\InvoiceCanAcceptPayment;
use App\StarterKits\StrukturaEngine\Actions\Guards\InvoiceCanBeArchived;
use App\StarterKits\StrukturaEngine\Actions\Guards\InvoiceCanBeCancelled;
use App\StarterKits\StrukturaEngine\Actions\Guards\InvoiceCanBeSent;
use App\StarterKits\StrukturaEngine\Actions\RecordPayment;
use App\StarterKits\StrukturaEngine\Actions\SendInvoice;

return [
    'enabled' => env('STRUKTURA_ENGINE_ENABLED', true),

    'navigation' => [
        'menu_key' => 'admin-sidebar',
    ],

    'dashboard' => [
        'widget_page_slug' => 'system-analytics',
    ],

    'engine_models' => [
        User::class => [
            'label' => 'Users',
            'surfaces' => [
                ComponentSurface::Page->value,
                ComponentSurface::Dashboard->value,
            ],
            'default_display_column' => 'name',
            'default_value_column' => 'id',
        ],
        Page::class => [
            'label' => 'Pages',
            'surfaces' => [
                ComponentSurface::Page->value,
                ComponentSurface::Navigation->value,
                ComponentSurface::Dashboard->value,
            ],
            'default_display_column' => 'title',
            'default_value_column' => 'id',
        ],
        Customer::class => [
            'label' => 'Customers',
            'surfaces' => [
                ComponentSurface::Page->value,
                ComponentSurface::Dashboard->value,
            ],
            'default_display_column' => 'name',
            'default_value_column' => 'id',
        ],
        Product::class => [
            'label' => 'Products',
            'surfaces' => [
                ComponentSurface::Page->value,
                ComponentSurface::Dashboard->value,
            ],
            'default_display_column' => 'name',
            'default_value_column' => 'id',
        ],
        Invoice::class => [
            'label' => 'Invoices',
            'surfaces' => [
                ComponentSurface::Page->value,
                ComponentSurface::Dashboard->value,
            ],
            'default_display_column' => 'invoice_number',
            'default_value_column' => 'id',
        ],
        InvoiceItem::class => [
            'label' => 'Invoice Items',
            'surfaces' => [
                ComponentSurface::Page->value,
            ],
            'default_display_column' => 'description',
            'default_value_column' => 'id',
        ],
        Payment::class => [
            'label' => 'Payments',
            'surfaces' => [
                ComponentSurface::Page->value,
                ComponentSurface::Dashboard->value,
            ],
            'default_display_column' => 'reference',
            'default_value_column' => 'id',
        ],
    ],

    'workflow' => [
        'guards' => [
            'invoiceCanBeSent' => InvoiceCanBeSent::class,
            'invoiceCanAcceptPayment' => InvoiceCanAcceptPayment::class,
            'invoiceCanBeCancelled' => InvoiceCanBeCancelled::class,
            'invoiceCanBeArchived' => InvoiceCanBeArchived::class,
        ],

        'models' => [
            Invoice::class => [
                'state_field' => 'status',
                'state_timestamps' => [
                    'sent' => 'sent_at',
                    'paid' => 'paid_at',
                    'archived' => 'archived_at',
                ],
                'state_colors' => [
                    'draft' => 'gray',
                    'sent' => 'warning',
                    'paid' => 'success',
                    'cancelled' => 'danger',
                    'archived' => 'gray',
                ],
                'actions' => [
                    'send_invoice' => [
                        'handler' => SendInvoice::class,
                        'guard' => 'invoiceCanBeSent',
                        'event' => 'invoice.send',
                        'from' => ['draft'],
                        'to' => 'sent',
                    ],
                    'record_payment' => [
                        'handler' => RecordPayment::class,
                        'guard' => 'invoiceCanAcceptPayment',
                        'event' => 'invoice.record-payment',
                        'from' => ['sent'],
                        'to' => 'paid',
                    ],
                    'cancel_invoice' => [
                        'handler' => CancelInvoice::class,
                        'guard' => 'invoiceCanBeCancelled',
                        'event' => 'invoice.cancel',
                        'from' => ['draft', 'sent'],
                        'to' => 'cancelled',
                    ],
                    'archive_invoice' => [
                        'handler' => ArchiveInvoice::class,
                        'guard' => 'invoiceCanBeArchived',
                        'event' => 'invoice.archive',
                        'from' => ['paid', 'cancelled'],
                        'to' => 'archived',
                    ],
                ],
            ],
        ],
    ],
];
