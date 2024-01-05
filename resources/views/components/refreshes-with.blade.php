@props(['method' => 'replace', 'scroll' => 'reset'])

<x-turbo::refresh-method :method="$method" />
<x-turbo::refresh-scroll :scroll="$scroll" />
