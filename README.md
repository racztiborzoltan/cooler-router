
# Other Router

***THIS PACKAGE IS UNDER DEVELOPMENT!!!***

Simple but not standard-thinking routing based on PSR-7. (At least, I think.)

## Some feature

- route reversing, create uri from route
- route processable checking
- route collecting
- route grouping
- route pattern support by RegexBasedRouteTrait class


## Why not a main stream?

- All route instance of MiddlewareInterface
- Each route defines its own
    - name
    - processable checking
    - route reversing, uri creating
    - ... business logic
- You can think of each implemented route as a controller

# Example

[example.php](examples/example.php)


#### English language

My English is under continuous development!
