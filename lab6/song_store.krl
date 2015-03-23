ruleset song_store {
  meta {
    name "Lab 6 - Song Store (Part 3)"
    description <<
Song Store: Ruleset for CS 452 Lab 6, Part 3
>>
    author "Tyler Duckworth"
    logging on
    sharing on
    provides songs, hymns, secular_music
  }

  global {
    songs = function() { 
      result = ent:songs || [];
      result.encode({"pretty" : "true"})
    } 

    hymns = function() { 
      result = ent:hymns || [];
      result.encode({"pretty" : "true"})
    }

    secular_music = function(x) { 
      result = ent:songs.filter(
        function(x) { 
          not x[0].match(re#god#i)
        });
      result.encode({"pretty" : "true"})
    }
  }

  rule collect_songs is active {
    select when explicit sung
    pre {
      all_songs = ent:songs || [];
      new_song = all_songs.union([[event:attr("song"), event:attr("song_time")]]);
    }
    noop();
    always {
      set ent:songs new_song;
      log "Added song: " + new_song.encode({"pretty" : "true"})
    }
  }

  rule collect_hymns is active {
    select when explicit found_hymn
    pre {
      all_hymns = ent:hymns || [];
      new_hymn = all_hymns.union([[event:attr("hymn"), event:attr("hymn_time")]]);
    }
    noop();
    always {
      set ent:hymns new_hymn;
      log "Added hymn: " + new_hymn.encode({"pretty" : "true"})
    }  
  }

  rule clear_songs is active {
    select when song reset 
    noop();
    always
    {
      clear ent:hymns;
      clear ent:songs;
      log "Songs cleared."
    }
  }
}
