ruleset see_songs_2a {
  meta {
    name "Lab 6 - See Songs (Part 2a)"
    description <<
See Songs: Ruleset for CS 452 Lab 6, Part 2a
>>
    author "Tyler Duckworth"
    logging on
    sharing on 
  }

  rule songs is active {
    select when echo message input "(.*)" setting(m) 
    	and echo message msg_type "song"
    send_directive("sing") with
      song = m;
  } 
}
