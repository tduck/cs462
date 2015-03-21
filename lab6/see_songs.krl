ruleset see_songs {
  meta {
    name "Lab 6 - See Songs (Part 1b)"
    description <<
See Songs: Ruleset for CS 452 Lab 6, Part 1b
>>
    author "Tyler Duckworth"
    logging on
    sharing on 
  }

  rule songs is active {
    select when echo message input "(.*)" setting(m)
    send_directive("sing") with
      song = m;
  } 
}
