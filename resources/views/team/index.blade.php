@extends('layouts.app')

@section('content')
    <div class="space-y-8">
        <section class="rounded-[2rem] card-soft p-8 shadow-glow ring-1 ring-slate-200/80">
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div>
                    <p class="text-sm uppercase tracking-[0.35em] text-blue-700">{{ __('ui.team_management') }}</p>
                    <h1 class="mt-2 text-4xl font-semibold text-slate-900">{{ __('ui.manage_team_access') }}</h1>
                    <p class="mt-3 max-w-2xl text-slate-600">{{ __('ui.only_owner_can_invite') }}</p>
                </div>
                <div class="grid gap-3 sm:grid-cols-3">
                    <div class="rounded-[1.75rem] bg-slate-50 p-5 text-center shadow-sm ring-1 ring-slate-200">
                        <p class="text-xs uppercase tracking-[0.35em] text-slate-500">{{ __('ui.team_members') }}</p>
                        <p class="mt-3 text-3xl font-semibold text-slate-900">{{ $members->count() }}</p>
                    </div>
                    <div class="rounded-[1.75rem] bg-slate-50 p-5 text-center shadow-sm ring-1 ring-slate-200">
                        <p class="text-xs uppercase tracking-[0.35em] text-slate-500">{{ __('ui.online_now') }}</p>
                        <p class="mt-3 text-3xl font-semibold text-emerald-700">{{ $members->filter(fn($member) => optional($member->last_login_at)->greaterThan(now()->subMinutes(15)))->count() }}</p>
                    </div>
                    <div class="rounded-[1.75rem] bg-slate-50 p-5 text-center shadow-sm ring-1 ring-slate-200">
                        <p class="text-xs uppercase tracking-[0.35em] text-slate-500">{{ __('ui.available_branches') }}</p>
                        <p class="mt-3 text-3xl font-semibold text-slate-900">{{ $branches->count() }}</p>
                    </div>
                </div>
            </div>
        </section>

        <section class="rounded-[2rem] card-soft p-6 shadow-glow ring-1 ring-slate-200/80">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                <div>
                    <h2 class="text-2xl font-semibold text-slate-900">{{ __('ui.current_team') }}</h2>
                    <p class="mt-3 text-slate-600">{{ __('ui.report_access_activity') }}</p>
                </div>
                <div class="rounded-full bg-blue-50 px-4 py-2 text-sm font-medium text-blue-700 ring-1 ring-blue-100">{{ __('ui.owner_access_only') }}</div>
            </div>

            <div class="mt-6 grid gap-4">
                @forelse ($members as $member)
                    @php
                        $teamPages = json_decode($member->pivot->allowed_pages ?? '[]', true);
                        $teamBranches = json_decode($member->pivot->allowed_locations ?? '[]', true);
                        $loginAt = optional($member->last_login_at);
                        $isOnline = $loginAt->greaterThan(now()->subMinutes(15));
                    @endphp
                    <div class="rounded-[2rem] card-soft p-6 shadow-sm ring-1 ring-slate-200">
                        <div class="flex flex-col gap-4 xl:flex-row xl:items-center xl:justify-between">
                            <div>
                                <p class="text-lg font-semibold text-slate-900">{{ $member->name }}</p>
                                <p class="text-sm text-slate-600">{{ $member->email }}</p>
                            </div>
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-medium text-slate-700">{{ __('ui.role_label') }} {{ ucfirst($roleNames[$member->pivot->role_id] ?? 'member') }}</span>
                                @if($isOnline)
                                    <span class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-medium text-emerald-700">{{ __('ui.online') }}</span>
                                @else
                                    <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-medium text-slate-700">{{ __('ui.last_login') }}: {{ $loginAt ? $loginAt->diffForHumans() : __('ui.never_signed_in') }}</span>
                                @endif
                            </div>
                        </div>

                        <div class="mt-5 grid gap-3 sm:grid-cols-2">
                            <div class="space-y-3 rounded-[1.75rem] bg-slate-50 p-4">
                                <p class="text-sm font-semibold text-slate-900">{{ __('ui.accessible_pages') }}</p>
                                <div class="flex flex-wrap gap-2">
                                    @forelse($teamPages as $pageKey)
                                        <span class="rounded-full bg-blue-50 px-3 py-1 text-xs font-medium text-blue-700">{{ __($pages[$pageKey] ?? 'ui.' . $pageKey) }}</span>
                                    @empty
                                        <p class="text-sm text-slate-500">{{ __('ui.no_pages_assigned') }}</p>
                                    @endforelse
                                </div>
                            </div>
                            <div class="space-y-3 rounded-[1.75rem] bg-slate-50 p-4">
                                <p class="text-sm font-semibold text-slate-900">{{ __('ui.allowed_branches') }}</p>
                                <div class="flex flex-wrap gap-2">
                                    @forelse($branches->whereIn('id', $teamBranches) as $branch)
                                        <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-medium text-slate-700">{{ $branch->name }}</span>
                                    @empty
                                        <p class="text-sm text-slate-500">{{ __('ui.no_branches_assigned') }}</p>
                                    @endforelse
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <p class="text-slate-600">{{ __('ui.no_team_members_yet') }}</p>
                @endforelse
            </div>
        </section>

        <section class="rounded-[2rem] card-soft p-6 shadow-glow ring-1 ring-slate-200/80">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-2xl font-semibold text-slate-900">{{ __('ui.add_a_team_member') }}</h2>
                    <p class="mt-2 text-slate-600">{{ __('ui.assign_pages_branches') }}</p>
                </div>
<div class="rounded-full bg-slate-50 px-4 py-2 text-sm text-slate-700 ring-1 ring-slate-200">{{ __('ui.secure_invite_flow') }}</div>
            </div>

            @if(session('success'))
                <div class="mt-5 rounded-[1.75rem] border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-700">{{ session('success') }}</div>
            @endif

            <form method="POST" action="{{ route('team.store') }}" class="mt-6 space-y-6">
                @csrf

                @if ($errors->any())
                    <div class="rounded-[1.75rem] border border-red-200 bg-red-50 p-4 text-sm text-red-700">
                        <ul class="list-disc space-y-1 pl-5">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="grid gap-4 lg:grid-cols-2">
                    <div>
                        <label class="text-sm font-medium text-slate-700">{{ __('ui.name') }}</label>
                        <input type="text" name="name" value="{{ old('name') }}" required class="input-base mt-2" />
                        @error('name')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="text-sm font-medium text-slate-700">{{ __('ui.email') }}</label>
                        <input type="email" name="email" value="{{ old('email') }}" required class="input-base mt-2" />
                        @error('email')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div class="grid gap-4 lg:grid-cols-2">
                    <div>
                        <label class="text-sm font-medium text-slate-700">{{ __('ui.password') }}</label>
                        <input type="password" name="password" class="input-base mt-2" />
                        @error('password')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
                        <p class="mt-2 text-xs text-slate-500">{{ __('ui.create_secure_password') }}</p>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-slate-700">{{ __('ui.confirm_password') }}</label>
                        <input type="password" name="password_confirmation" class="input-base mt-2" />
                    </div>
                </div>

                <div class="grid gap-4 lg:grid-cols-2">
                    <div>
                        <label class="text-sm font-medium text-slate-700">{{ __('ui.role') }}</label>
                        <select name="role_id" class="input-base mt-2 bg-slate-50 text-slate-900">
                            @foreach($roles as $role)
                                <option value="{{ $role->id }}" @selected(old('role_id') == $role->id)>{{ ucfirst($role->name) }}</option>
                            @endforeach
                        </select>
                        @error('role_id')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <p class="text-sm font-medium text-slate-700">{{ __('ui.page_access') }}</p>
                        <p class="mt-1 text-xs text-slate-500">{{ __('ui.select_at_least_one_page') }}</p>
                        <div class="mt-3 grid gap-3 sm:grid-cols-2">
                            @foreach($pages as $pageKey => $pageLabel)
                                <label class="inline-flex items-center gap-2 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-700">
                                    <input type="checkbox" name="pages[]" value="{{ $pageKey }}" class="h-4 w-4 rounded border-slate-300 text-blue-600" {{ in_array($pageKey, old('pages', [])) ? 'checked' : '' }}>
                                    {{ __($pageLabel) }}
                                </label>
                            @endforeach
                        </div>
                        @error('pages')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div>
                    <p class="text-sm font-medium text-slate-700">{{ __('ui.branch_access') }}</p>
                    <p class="mt-1 text-xs text-slate-500">{{ __('ui.grant_access_locations') }}</p>
                    <div class="mt-3 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                        @forelse($branches as $branch)
                            <label class="inline-flex items-center gap-2 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-700">
                                <input type="checkbox" name="locations[]" value="{{ $branch->id }}" class="h-4 w-4 rounded border-slate-300 text-blue-600" {{ in_array($branch->id, old('locations', [])) ? 'checked' : '' }}>
                                {{ $branch->name }}
                            </label>
                        @empty
                            <p class="text-sm text-slate-500">{{ __('ui.no_branches_available') }}</p>
                        @endforelse
                    </div>
                    @error('locations')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <button type="submit" class="inline-flex items-center justify-center rounded-full btn-primary px-8 py-3 text-sm font-semibold shadow-glow">{{ __('ui.add_team_member') }}</button>
            </form>
        </section>
    </div>
@endsection
