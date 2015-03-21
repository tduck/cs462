ruleset part1a {
  meta {
    name "Lab 6 - Part 1a"
    description <<
Ruleset for CS 452 Lab 6, Part 1a
>>
    author "Tyler Duckworth"
    logging on
    sharing on 
  }
  
  rule hello is active {
    select when echo hello
    send_directive("say") with
      something = "Hello World";
  }

  rule message is active {
    select when echo message input "(.*)" setting(m)
    send_directive("say") with
      something = m;
  } 
}
