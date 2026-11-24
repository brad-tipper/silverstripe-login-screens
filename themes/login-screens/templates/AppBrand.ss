<% if $LoginLogoUrl %>
    <img class="app-brand__logo<% if $LoginLogoDarkUrl %> app-brand__logo--light-mode<% end_if %>" src="$LoginLogoUrl" alt="$SiteConfig.Title.ATT">
    <% if $LoginLogoDarkUrl %>
        <img class="app-brand__logo app-brand__logo--dark-mode" src="$LoginLogoDarkUrl" alt="$SiteConfig.Title.ATT">
    <% end_if %>
<% end_if %>
