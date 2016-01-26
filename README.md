A simple BB/Forum for ProcessWire.

Work in progress.
Not recommended for use on live sites.
You MUST remember to sanitize your messages before you save them.
Feedback welcome.

Installation and setup:

1. Download and install HermodBB.
2. Configure the required settings. I recommend that you change these settings, even if you decide not to use them. The settings will be referred to in the example code.
3. Click submit and go to your root admin pagetree.
4. Navigate to where you want the forum to be located and add a new page (for this example we will keep it at root level).
5. Select the "HBB Forum Root" Template and give it a name, i.e Forum, then save and publish.
6. Click on the "Children" tab and add a new page. Now input the name of your first forum category. For this example we will type in Guests and click save.
7. Now under "View Forum Category", click on guest and publish the page. This will ensure this forum category will be accessible to all visitors. Obviously you can change this to your needs.
8. Click on the "Children" tab and add a new page. Type in "A forum for guests" as the title and click save.
9. You will now be able to type in the forum rules, and select who is able to view the actual forum. Ensure the role guest has permission to start topics, view comments and post comments. This concludes our back-end example.
10. Now we need to create a couple of templates to ensure that this is actually functional. For convenience I have included some templates for reference. They use UIKit for the markup, but are not totally complete as I have been using them for testing purposes. They should however be easy enough to update for your needs.
