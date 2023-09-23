{{ turbo_stream([
    turbo_stream()->append("posts", "Hello World"),
    turbo_stream()->remove("post_123"),
]) }}
