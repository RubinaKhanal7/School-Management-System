@hasanyrole('Super Admin|District Admin|Municipality Admin|Head School|School Admin|Teacher')
    <li class="nav-item">
        <div class="row navbar-vertical-label-wrapper mt-3 mb-2">
            <div class="col-auto navbar-vertical-label">{{ __('Accounting Module') }}</div>
            <div class="col ps-0">
                <hr class="mb-0 navbar-vertical-divider">
            </div>
        </div>
    </li>

    <li class="nav-item">
        <a class="nav-link dropdown-indicator" href="#accountingModuleDropdown" role="button" data-bs-toggle="collapse" aria-expanded="true" aria-controls="accountingModuleDropdown">
            <div class="d-flex align-items-center">
                <span class="nav-link-icon"><i class="fas fa-credit-card"></i></span>
                <span class="nav-link-text ps-1">{{ __("Accounting Module") }}</span>
            </div>
        </a>
        
        <ul class="nav collapse {{ Request::segment(2) == 'fiscal-years' || Request::segment(2) == 'voucher_types' ? 'show' : '' }}" id="accountingModuleDropdown">
            @can('view_fiscal_years')
                <li class="nav-item">
                    <a class="nav-link {{ Request::segment(2) == 'fiscal-years' ? 'active' : '' }}" href="{{ route('admin.fiscal-years.index') }}">
                        <div class="d-flex align-items-center">
                            <i class="fa fa-angle-double-right"></i> {{ __('Fiscal Year List') }}
                        </div>
                    </a>
                </li>
            @endcan

            @can('view_voucher_types')
                <li class="nav-item">
                    <a class="nav-link {{ Request::segment(2) == 'voucher_types' ? 'active' : '' }}" href="{{ route('admin.voucher_types.index') }}">
                        <div class="d-flex align-items-center">
                            <i class="fa fa-angle-double-right"></i> {{ __('Voucher Types List') }}
                        </div>
                    </a>
                </li>
            @endcan

            @can('view_accounts')
                <li class="nav-item">
                    <a class="nav-link {{ Request::segment(2) == 'view_accounts' ? 'active' : '' }}" href="{{ route('admin.accounts.index') }}">
                        <div class="d-flex align-items-center">
                            <i class="fa fa-angle-double-right"></i> {{ __('Accounts List') }}
                        </div>
                    </a>
                </li>
            @endcan

            @can('view_transaction')
                <li class="nav-item">
                    <a class="nav-link {{ Request::segment(2) == 'view_transaction' ? 'active' : '' }}" href="{{ route('admin.transactions.index') }}">
                        <div class="d-flex align-items-center">
                            <i class="fa fa-angle-double-right"></i> {{ __('Transaction List') }}
                        </div>
                    </a>
                </li>
            @endcan

            @can('view_ledger')
                <li class="nav-item">
                    <a class="nav-link {{ Request::segment(2) == 'view_ledger' ? 'active' : '' }}" href="{{ route('admin.ledgers.index') }}">
                        <div class="d-flex align-items-center">
                            <i class="fa fa-angle-double-right"></i> {{ __('Ledger List') }}
                        </div>
                    </a>
                </li>
            @endcan
        </ul>
    </li>
@endhasanyrole
