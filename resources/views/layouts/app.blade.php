<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Система управления клиентами')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .sidebar {
            min-height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        .sidebar .nav-link {
            color: rgba(255, 255, 255, 0.8);
            border-radius: 10px;
            margin: 2px 0;
            transition: all 0.3s ease;
        }

        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            background-color: rgba(255, 255, 255, 0.2);
            color: white;
            transform: translateX(5px);
        }

        .card {
            border: none;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            border-radius: 15px;
            transition: transform 0.3s ease;
        }

        .card:hover {
            transform: translateY(-5px);
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 25px;
        }

        .btn-success {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            border: none;
            border-radius: 25px;
        }

        .btn-danger {
            background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
            border: none;
            border-radius: 25px;
        }

        .table-responsive {
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .badge {
            border-radius: 20px;
        }
    </style>
</head>

<body class="bg-light">
    <div class="container-fluid">
        <div class="row">
            @auth
            <nav class="col-md-3 col-lg-2 d-md-block sidebar collapse">
                <div class="position-sticky pt-3">
                    <div class="text-center mb-4">
                        <h5 class="text-white">
                            @if(auth()->user()->hasRole('super-admin'))
                            <i class="bi bi-shield-check"></i> Супер-админ
                            @elseif(auth()->guard('client')->check())
                            <i class="bi bi-building"></i> Клиент
                            @elseif(auth()->guard('employee')->check())
                            <i class="bi bi-person"></i> Сотрудник
                            @endif
                        </h5>
                        <small class="text-white-50">
                            @if(auth()->user())
                            {{ auth()->user()->name }}
                            @elseif(auth()->guard('client')->check())
                            {{ auth()->guard('client')->user()->name }}
                            @elseif(auth()->guard('employee')->check())
                            {{ auth()->guard('employee')->user()->name }}
                            @endif
                        </small>
                    </div>

                    <ul class="nav flex-column">
                        @if(auth()->user() && auth()->user()->hasRole('super-admin'))
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('super-admin.dashboard') ? 'active' : '' }}"
                                href="{{ route('super-admin.dashboard') }}">
                                <i class="bi bi-speedometer2"></i> Панель управления
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('super-admin.clients*') ? 'active' : '' }}"
                                href="{{ route('super-admin.clients') }}">
                                <i class="bi bi-building"></i> Клиенты
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('super-admin.employees*') ? 'active' : '' }}"
                                href="{{ route('super-admin.employees') }}">
                                <i class="bi bi-people"></i> Сотрудники
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('super-admin.integrations*') ? 'active' : '' }}"
                                href="{{ route('super-admin.integrations.index') }}">
                                <i class="bi bi-gear"></i> Интеграции
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('super-admin.activity-log') ? 'active' : '' }}"
                                href="{{ route('super-admin.activity-log') }}">
                                <i class="bi bi-activity"></i> Лог активности
                            </a>
                        </li>
                        @elseif(auth()->guard('client')->check())
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('client.dashboard') ? 'active' : '' }}"
                                href="{{ route('client.dashboard') }}">
                                <i class="bi bi-speedometer2"></i> Панель управления
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('client.employees*') ? 'active' : '' }}"
                                href="{{ route('client.employees.index') }}">
                                <i class="bi bi-people"></i> Мои сотрудники
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('client.integrations*') ? 'active' : '' }}"
                                href="{{ route('client.integrations.index') }}">
                                <i class="bi bi-gear"></i> Интеграции
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('client.activity-log') ? 'active' : '' }}"
                                href="{{ route('client.activity-log') }}">
                                <i class="bi bi-activity"></i> Лог активности
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('client.notifications*') ? 'active' : '' }}"
                                href="{{ route('client.notifications') }}">
                                <i class="bi bi-bell"></i> Уведомления
                                @if(auth()->guard('client')->check() && auth()->guard('client')->user()->notifications()->unread()->count() > 0)
                                <span class="badge bg-warning text-dark ms-1">
                                    {{ auth()->guard('client')->user()->notifications()->unread()->count() }}
                                </span>
                                @endif
                            </a>
                        </li>
                        @elseif(auth()->guard('employee')->check())
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('employee.dashboard') ? 'active' : '' }}"
                                href="{{ route('employee.dashboard') }}">
                                <i class="bi bi-speedometer2"></i> Панель управления
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('employee.profile*') ? 'active' : '' }}"
                                href="{{ route('employee.profile.edit') }}">
                                <i class="bi bi-person-circle"></i> Мой профиль
                            </a>
                        </li>
                        @endif

                        <li class="nav-item mt-3">
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="nav-link border-0 bg-transparent text-start w-100">
                                    <i class="bi bi-box-arrow-right"></i> Выйти
                                </button>
                            </form>
                        </li>
                    </ul>
                </div>
            </nav>
            @endauth

            <main class="@auth col-md-9 ms-sm-auto col-lg-10 @endauth px-md-4">
                @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show mt-3" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                @endif

                @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show mt-3" role="alert">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                @endif

                @if(session('token'))
                <div class="alert alert-info alert-dismissible fade show mt-3" role="alert">
                    <strong>Новый токен:</strong>
                    <code>{{ session('token') }}</code>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    @yield('scripts')
</body>

</html>