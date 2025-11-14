{{-- Custom GraphiQL view with default headers support. --}}
@php
use MLL\GraphiQL\GraphiQLAsset;

    $path = '/' . trim(request()->path(), '/');
    $routeConfig = (array) config("graphiql.routes.{$path}");
    $defaultHeaders = $routeConfig['headers'] ?? [];
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>GraphiQL</title>
    <style>
        body { margin: 0; overflow: hidden; }
        #graphiql { height: 100dvh; }
        #graphiql-loading { height: 100%; display: flex; align-items: center; justify-content: center; font-size: 4rem; }
        .docExplorerWrap { overflow: auto !important; }
    </style>
    <script src="{{ GraphiQLAsset::reactJS() }}"></script>
    <script src="{{ GraphiQLAsset::reactDOMJS() }}"></script>
    <link rel="stylesheet" href="{{ GraphiQLAsset::graphiQLCSS() }}"/>
    <link rel="stylesheet" href="{{ GraphiQLAsset::pluginExplorerCSS() }}"/>
    <link rel="shortcut icon" href="{{ GraphiQLAsset::favicon() }}"/>
</head>

<body>

<div id="graphiql">
    <div id="graphiql-loading">Loading…</div>
</div>

<script src="{{ GraphiQLAsset::graphiQLJS() }}"></script>
<script src="{{ GraphiQLAsset::pluginExplorerJS() }}"></script>
<script>
    const endpointUrl = '{{ $url }}';
    const subscriptionUrl = '{{ $subscriptionUrl }}';
    const defaultHeaders = {!! json_encode($defaultHeaders, JSON_THROW_ON_ERROR) !!};

    const baseFetcher = GraphiQL.createFetcher({
        url: endpointUrl,
        subscriptionUrl,
    });

    const fetcher = async (graphQLParams, opts = {}) => {
        const headers = {
            ...(defaultHeaders || {}),
            ...(opts.headers || {}),
        };

        return baseFetcher(graphQLParams, { ...opts, headers });
    };

    const explorer = GraphiQLPluginExplorer.explorerPlugin();

    function GraphiQLWithExplorer() {
        return React.createElement(GraphiQL, {
            fetcher,
            headers: defaultHeaders && Object.keys(defaultHeaders).length
                ? JSON.stringify(defaultHeaders, null, 2)
                : undefined,
            plugins: [explorer],
        });
    }

    ReactDOM.render(
        React.createElement(GraphiQLWithExplorer),
        document.getElementById('graphiql'),
    );
</script>

</body>
</html>
