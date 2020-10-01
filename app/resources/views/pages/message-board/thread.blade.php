@extends('layouts.master')

@section('page-header', 'Message Board')

@section('content')
    <div class="row">

        <div class="col-sm-12 col-md-9">
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="ra ra-wooden-sign"></i> Message Board Thread: {{ $thread->title }}</h3>
                    <div class="pull-right">
                        <a href="{{ route('message-board.category', $thread->category->slug) }}"><i class="fa fa-chevron-left"></i><i class="fa fa-chevron-left"></i></a>
                    </div>
                </div>
                <div class="box-header with-border">
                    <div class="user-block pull-left">
                        @php
                            $ranking = [];
                        @endphp
                        <i class="ra {{ $ranking && $ranking['title_icon'] ? $ranking['title_icon'] : 'ra-player' }} text-muted pull-left" title="{{ $ranking ? $ranking['name'] : null }}" style="font-size: 36px;"></i>
                        <span class="username">
                            {{ $thread->user->display_name }} {!! $thread->user->displayRoleHtml() !!}
                            @if ($ranking && $ranking['title'])
                                <em data-toggle="tooltip" title="{{ $ranking['name'] }}">{{ $ranking['title'] }}</em>
                            @endif
                        </span>
                        <span class="description">
                            posted at {{ $thread->created_at }}
                        </span>
                    </div>
                    <div class="box-tools">
                        @if ($user->id == $thread->user->id)
                            <a href="{{ route('message-board.delete.thread', $thread) }}"><i class="fa fa-trash text-red"></i></a>
                        @else
                            <a href="{{ route('message-board.flag.thread', $thread) }}" title="Report Abuse"><i class="fa fa-flag text-red"></i></a>
                        @endif
                    </div>
                </div>
                <div class="box-body">
                    @if ($thread->flagged_for_removal)
                        <p class="text-danger"><i>This post has been flagged for removal.</i></p>
                    @else
                        {!! Markdown::convertToHtml($thread->body) !!}
                    @endif
                </div>
                @if (!$thread->posts->isEmpty())
                    <div class="box-footer box-comments">
                        @foreach ($thread->posts as $post)
                            <div class="box-comment">
                                @php
                                    $ranking = [];
                                @endphp
                                <i class="ra {{ $ranking && $ranking['title_icon'] ? $ranking['title_icon'] : 'ra-player' }} text-muted pull-left" title="{{ $ranking ? $ranking['name'] : null }}" style="font-size: 26px;"></i>
                                <div class="comment-text">
                                    <span class="username">
                                        {{ $post->user->display_name }} {!! $post->user->displayRoleHtml() !!}
                                        @if ($ranking && $ranking['title'])
                                            <em data-toggle="tooltip" title="{{ $ranking['name'] }}">{{ $ranking['title'] }}</em>
                                        @endif
                                        <span class="text-muted pull-right">
                                            {{ $post->created_at }}&nbsp;
                                            @if ($user->id == $post->user->id)
                                                <a href="{{ route('message-board.delete.post', $post) }}"><i class="fa fa-trash text-red"></i></a>
                                            @else
                                                <a href="{{ route('message-board.flag.post', $post) }}" title="Report Abuse"><i class="fa fa-flag text-red"></i></a>
                                            @endif
                                        </span>
                                    </span>
                                    @if ($post->flagged_for_removal)
                                        <p class="text-danger"><i>This post has been flagged for removal.</i></p>
                                        @include('partials.forum-rules')
                                    @else
                                        {!! Markdown::convertToHtml($post->body) !!}
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif

                <form action="{{ route('message-board.reply', $thread) }}" method="post" class="form-horizontal" role="form">
                    @csrf
                    <div class="box-footer">
                        <label for="body" class="col-sm-2 control-label">Post Reply</label>
                        <div class="col-sm-10">
                            <textarea name="body" id="body" rows="3" class="form-control" placeholder="Body" required>{{ old('body') }}</textarea>
                            <p class="help-block">Markdown is supported with <a href="http://commonmark.org/help/" target="_blank">CommonMark syntax <i class="fa fa-external-link"></i></a>.</p>
                        </div>
                    </div>

                    <div class="box-footer">
                        <button type="submit" class="btn btn-primary">Post Reply</button>
                        <p class="help-block pull-right">
                            You are posting as <b>{{ $user->display_name }}</b>.
                        </p>
                    </div>
                </form>
            </div>
        </div>

        <div class="col-sm-12 col-md-3">
            <div class="box">
                <div class="box-header with-border">
                    <h3 class="box-title">Information</h3>
                </div>
                <div class="box-body">
                    <p>The message board is where you can communicate with other players. All registered users can view and post here.</p>
                    <p>There {{ ($thread->posts->count() === 1) ? 'is' : 'are' }} {{ number_format($thread->posts->count()) }} {{ str_plural('reply', $thread->posts->count()) }} in this category.</p>
                    @include('partials.forum-rules')
                </div>
            </div>
        </div>

    </div>
@endsection
