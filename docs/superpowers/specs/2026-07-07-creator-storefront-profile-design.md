# Creator Storefront Profile Design

## Goal

Turn the public creator profile page at `/user/{username}` into a storefront entry page for sellers.

## Scope

This pass affects the basic theme public profile index page only. Existing routes for portfolio, followers, following, and reviews keep their current profile header and sidebar layout.

## Design

The profile index uses a two-column storefront layout on desktop. The left column is a compact creator card with cover image, avatar, availability, follow/message/portfolio actions, bio, social links, and simple item/sales/review stats. The right column is the storefront area with a heading, item/about navigation, a two-column grid of approved items, and compact product cards with preview, price, title, rating, and sales metadata.

On tablet and mobile, the layout stacks into one column and the item grid collapses to one column. The old oversized profile hero is hidden only on the profile index route.

## Data

`ProfileController@index` loads the existing user, followers, and the creator's approved items. The item query eager-loads author and category because item cards may need those relationships.

## Testing

Add a PHPUnit string guard that verifies the controller item query, storefront Blade markers, and CSS marker are present. Verify the live page returns `200` and includes the storefront markup.
