ruleset see_songs_2b {
  meta {
    name "Lab 6 - See Songs (Part 2b)"
    description <<
See Songs: Ruleset for CS 452 Lab 6, Part 2b
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
    always {
      raise explicit event "sung" with song = m;
      log "Songs: #{m} "
    }
  } 

  rule find_hymn is active {
    select when explicit sung 
    pre
    {
      song_title = event:attr("song");
    }
    send_directive("find_hymn") with
      hymn = song_title;
    always {
      if (song_title.match(re#god#i))
      {
        raise explicit event "found_hymn" with hymn = song_title;
      }
      log "Find_hymn song: #{song_title} "
    }
  }
}
