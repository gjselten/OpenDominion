@extends('layouts.staff')

@section('page-header', "Dominion: {$dominion->name}")

@section('content')
    <div class="box">
        <div class="box-header">
            <h3 class="box-title">{{ $dominion->name }} (#{{ $dominion->realm->number }})</h3>
        </div>
        <div class="box-body table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th># Logins</th>
                        <th># IPs Used</th>
                        <th># Users with shared IPs</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>{{ $userLogins }}</td>
                        <td>{{ $ipsUsedCount }}</td>
                        <td>{{ $otherUserCount }}</td>
                        <td><a href="{{ route('staff.moderator.dominion.activity', [$dominion->id]) }}">Investigate</a></td>
                    </tr>
                </tbody>
            </table>

            <h4>Game Events</h4>

            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>
                            Date
                        </th>
                        <th>
                            Source
                        </th>
                        <th>
                            Target
                        </th>
                        <th>
                            Result
                        </th>
                        <th>
                            &nbsp;
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($gameEvents as $gameEvent)
                        @if ($gameEvent->type !== 'invasion')
                            @continue
                        @endif
                        <tr>
                            <td>
                                <span>{{ $gameEvent->created_at }}</span>
                            </td>
                            <td>
                                {{ $gameEvent->source->name }} (#{{ $gameEvent->source->realm->number }})
                            </td>
                            <td>
                                {{ $gameEvent->target->name }} (#{{ $gameEvent->target->realm->number }})
                            </td>
                            <td>
                                @if ($gameEvent->data['result']['success'])
                                    Success
                                @else
                                    Failure
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('staff.moderator.dominion.event', [$dominion->id, $gameEvent->id]) }}">Investigate</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="box-footer">
            @if ($dominion->locked_at !== null)
                <div class="pull-left">
                    Locked at {{ $dominion->locked_at }}
                </div>
                <div class="pull-right">
                    <form action="{{ route('staff.moderator.dominion.unlock', [$dominion]) }}" method="post">
                        @csrf
                        <button class="btn btn-primary">Unlock Dominion</a>
                    </form>
                </div>
            @else
                <div class="pull-right">
                    <form action="{{ route('staff.moderator.dominion.lock', [$dominion]) }}" method="post">
                        @csrf
                        <button class="btn btn-danger">Lock Dominion</a>
                    </form>
                </div>
            @endif
        </div>
    </div>
@endsection
