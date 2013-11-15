# Adapted by Ryan Florence (http://ryanflorence.com) 
# original by Chris Dinger: http://www.houseofding.com/2009/03/create-an-rss-feed-of-your-git-commits/
# 
# Takes one, two, or three arguments
# 1. Repository path (required) - the path to the repository
# 2. The url to put as the <link> for both channel and items
# 3. the repository name, defaults to directory name of the repository
#
# Command line usage:
# ruby gitrss.rb /path/to/repo > feed.rss
# ruby gitrss.rb /path/to/repo http://example.com > feed.rss
# ruby gitrss.rb /path/to/repo http://example.com repo_name > feed.rss

repository_path = $*[0]
url = $*[1] || 'http://example.com/rss/'
Dir.chdir repository_path
repository_name = $*[2] || `pwd`.split('/').last.chop

git_history = `git log --max-count=10 --name-status`
entries = git_history.split("\ncommit ")

rss = "<?xml version=\"1.0\" encoding=\"UTF-8\" ?>
  <rss version=\"2.0\">

  <channel>
    <title>#{repository_name} commits</title>
    <description>Git commits to #{repository_name}</description>
    <link>#{url}</link>
    <lastBuildDate>#{Time.now}</lastBuildDate>
    <pubDate>#{Time.now}</pubDate>
"

entries.each do |entry|
  guid = entry.gsub(/^.*commit /ms, '').gsub(/\n.*$/ms, '')
  author_name = entry.gsub(/^.*Author: /ms, '').gsub(/ <.*$/ms, '')
  date = entry.gsub(/^.*Date: +/ms, '').gsub(/\n.*$/ms, '')
  comments = entry.gsub(/^.*Date[^\n]*/ms, '')

  rss += "
    <item>
      <title>Commit by #{author_name}</title>
      <description>#{author_name} made a commit on #{date}</description>
      <content><![CDATA[
        <table border=0>
          <tr><td align=right><b>SHA</b></td><td>#{guid}</td></tr>
          <tr><td align=right><b>Author</b></td><td>#{author_name}</td></tr>
          <tr><td align=right><b>Date</b></td><td>#{date}</td></tr>
        </table>
        <pre>#{comments}</pre>
      ]]></content>
      <link>#{url}</link>
      <guid isPermaLink=\"false\">#{guid}</guid>
      <pubDate>#{date}</pubDate>
    </item>"
end 

rss += "
  </channel>
</rss>"

puts rss
