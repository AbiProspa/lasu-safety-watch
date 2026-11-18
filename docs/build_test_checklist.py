"""Builds docs/LASU_Safety_Watch_Test_Checklist.html (print to PDF with Edge).
   python docs/build_test_checklist.py
"""
import html
from pathlib import Path

HERE = Path(__file__).parent
URL = "https://lasu-safety-watch-production-56433e86.up.railway.app"

ACCOUNTS = [
    ("Administrator", "admin@lasu.test", "Admin@1234", "Everything, incl. Users & roles"),
    ("Campus Security", "security@lasu.test", "Security@123", "Security + SOS reports"),
    ("Security officer", "bello@lasu.test", "Security@123", "Security + SOS reports"),
    ("Works & Services", "works@lasu.test", "Works@1234", "Hostel, Facility, Environment"),
    ("Medical Centre", "medical@lasu.test", "Medical@123", "Health & Emergency"),
    ("Counselling", "counsel@lasu.test", "Counsel@123", "Harassment & GBV (confidential)"),
    ("Student", "student@lasu.test", "Student@123", "Public site + My reports"),
]

# (section title, intro, [(what to do, expected result), ...])
SECTIONS = [
    ("1. General & navigation", "Open the live site on a laptop, then repeat key checks on a phone.", [
        ("Open the home page.", "Page loads over https with the LASU crest, navy colours and no errors."),
        ("Look at the browser tab.", "The crest icon (favicon) shows; title reads \"LASU Safety Watch\"."),
        ("Click each navbar link: Report incident, Live map, Safety Center, Track report.", "Each page opens; the current page is highlighted in the navbar."),
        ("Click the logo from any page.", "Returns to the home page."),
        ("Click the red Emergency SOS button in the navbar.", "Opens the Safety Center and scrolls to the SOS panel."),
        ("On a phone, tap the menu (☰) icon.", "Menu opens with the 4 links + Log in; every item is easy to tap."),
        ("Scroll to the footer and tap an emergency number.", "Phone dialler opens with that number."),
        ("Visit a page that does not exist, e.g. /abc.", "A \"Not Found\" page appears (no crash / no code shown)."),
    ]),
    ("2. Home page", None, [
        ("Read the hero section.", "Headline \"Report it. We'll act on it.\", one Report an Incident button and a Track existing report link."),
        ("Check the \"What do you need to report?\" card.", "6 categories with the unit that handles each and a live \"x open\" count; \"Live\" indicator and last-report time."),
        ("Tap a category (e.g. Security Incident).", "It expands to show its specific types (Theft, Cult activity, …). Tapping again closes it."),
        ("Tap a specific type (e.g. Cult activity).", "Report form opens with that category and type already selected."),
        ("Check the numbers strip under the hero.", "Shows reports received, resolved, this week, average resolution time."),
        ("Check \"Recently verified\" list.", "Only verified/in-progress/resolved reports; never Harassment & GBV; clicking one opens its tracking page."),
        ("Click \"Open live map\".", "Goes to the Live map."),
    ]),
    ("3. Report an incident (no login)", "Log out first so you test as an anonymous visitor.", [
        ("Open Report incident and click Submit without filling anything.", "Form is not sent; required fields (type, title, description, location) are flagged."),
        ("Choose each of the 6 categories in turn.", "The \"Specific type\" list changes to match the category."),
        ("Pick a severity (Low / Medium / High / Critical).", "Selected option is highlighted."),
        ("Type a title shorter than 5 characters and submit.", "Error message asks for a longer title."),
        ("Choose a Campus location (e.g. Senate Building).", "The map jumps to that place and drops a red pin."),
        ("Tap somewhere else on the map, then drag the pin.", "Pin moves; message confirms the new position."),
        ("Click \"Use my location\" and allow location.", "Pin moves to your real position. If you deny, a friendly message says to tap the map."),
        ("Set \"When did it happen?\" to a future time.", "Not allowed (date must be now or earlier)."),
        ("Attach a photo under 5 MB.", "A small preview appears next to the upload box."),
        ("Attach a non-image file (e.g. a PDF) or an image over 5 MB.", "Submission is rejected with a clear error."),
        ("Tick \"Report anonymously\" and submit a full report.", "Success page with a tracking code like LSW-XXXXXX and a Copy code button."),
        ("Click \"Copy code\", then \"Track this report\".", "Code is copied; tracking page shows status \"Received\" and the first update."),
        ("Start a Harassment & GBV report.", "\"Report anonymously\" is ticked and locked; a note says these reports are always anonymous."),
        ("Fill in the optional follow-up contact and submit.", "Report is saved; only the responsible unit can see the contact."),
        ("Submit more than 10 reports within one minute.", "Extra submissions are blocked (\"Too many requests\") - spam protection."),
    ]),
    ("4. Track a report", None, [
        ("Enter a valid code on Track report.", "Shows category, location, date, a 4-step progress bar and the update timeline."),
        ("Enter the same code in lower case (lsw-xxxxxx).", "Still found."),
        ("Enter a wrong code.", "Friendly \"No report found\" message."),
        ("Track a Harassment & GBV report.", "Title is hidden (\"Confidential report\")."),
        ("After staff add an internal note (section 8), track the report again.", "Internal notes are NOT shown; public notes are."),
        ("Track a report that staff marked Rejected.", "Message explains it was closed without action."),
    ]),
    ("5. Live map", None, [
        ("Open Live map.", "Map of LASU Ojo campus with coloured pins; larger dots for critical reports."),
        ("Click a pin.", "Popup shows type, title, location, time, status and a link to its tracking page."),
        ("Click a category in the filter list.", "Those pins disappear/reappear; counts beside each category are correct."),
        ("Change \"All time\" to \"Last 7 days\".", "Only recent reports remain."),
        ("Submit a new report, then look for it on the map.", "It does NOT appear until staff verify it (pending reports are private)."),
        ("Look for any Harassment & GBV report.", "Never shown on the map, even when verified."),
    ]),
    ("6. Safety Center & SOS", "Use a phone for these if possible. Tell Campus Security first if testing on the real system.", [
        ("Open Safety Center.", "Compact header, SOS panel (only the SOS button is red) and \"Who to call\" list."),
        ("Tap SOS, then tap Cancel during the countdown.", "Countdown 5→0 shows; Cancel stops it and nothing is sent."),
        ("Tap SOS and let it finish (allow location).", "\"Alert sent to Campus Security\" with a reference code; exact GPS is used."),
        ("Repeat but block location permission.", "Alert still sends, with a message that the location is approximate."),
        ("Type a note (e.g. \"Fight at Hostel B gate\") before tapping SOS.", "The note appears in the SOS report on the staff side."),
        ("Tap SOS more than 5 times within a minute.", "Extra alerts are blocked; message asks you to call Campus Security."),
        ("Tap \"Can't use SOS? Call Campus Security\" and each number in Who to call.", "Phone dialler opens with the right number."),
        ("Read \"SOS or report?\" and Stay safe on campus.", "Content is readable; Report an Incident button opens the form."),
    ]),
    ("7. Accounts (students)", None, [
        ("Register with a weak password (e.g. \"abc\").", "Rejected: needs 8+ characters with letters and numbers."),
        ("Register with mismatched password confirmation.", "Rejected with an error."),
        ("Register with an email that already exists.", "Rejected: email already taken."),
        ("Register correctly.", "Logged in automatically; welcome message; navbar shows My reports."),
        ("Submit a report while logged in (not anonymous), then open My reports.", "The report is listed with its status."),
        ("Submit an anonymous report while logged in, then open My reports.", "It is NOT listed (anonymous reports are not linked to you)."),
        ("Log out, then log in with a wrong password.", "\"Incorrect email or password\"; after several fast attempts, login is temporarily blocked."),
        ("Log in as student@lasu.test, then open /dashboard in the address bar.", "Access denied (403) - students cannot see the staff area."),
    ]),
    ("8. Staff dashboard & case handling", "Log in as security@lasu.test.", [
        ("Log in.", "Goes straight to the Overview dashboard; sidebar says Campus Security."),
        ("Check the KPI cards.", "Total, Pending review, Active cases, Resolved this month, Avg. resolution time."),
        ("Check the charts.", "14-day trend line, By category doughnut, By status bar chart all display."),
        ("Check Hotspots and Needs attention.", "Top locations with bars; open reports sorted by severity (critical first)."),
        ("Keep the dashboard open and send an SOS from a phone.", "Within ~15 seconds a red banner \"1 active SOS alert\" appears with a beep."),
        ("Click the SOS alert in the banner.", "Opens that SOS report with its map location."),
        ("Open Reports and try each filter (status, category, severity, dates) and the search box.", "List updates correctly; search by title, code or place works."),
        ("Click Export CSV.", "A .csv file downloads and opens in Excel with the filtered reports."),
        ("Open a report.", "Shows details, reporter (or Anonymous), contact, photo (click to enlarge), map and timeline."),
        ("Click \"Get directions\".", "Google Maps opens with directions to the location."),
        ("Change status to Verified, assign an officer, add a public note, Save.", "\"Report updated\" message; timeline shows who did it; tracking page shows the public note."),
        ("Add a note with \"Internal note\" ticked.", "Timeline marks it Internal (amber); it does not appear on the public tracking page."),
        ("Set status to Resolved.", "Resolved time is recorded; report counts as resolved on dashboard and home page."),
        ("Change severity.", "New severity badge shows in list and details."),
        ("Open a Hostel report's URL as security (e.g. from the admin list).", "Access denied (403) - units only see their own reports."),
    ]),
    ("9. Other units", None, [
        ("Log in as works@lasu.test.", "Sees only Hostel, Facility Damage and Environment & Flooding reports; no SOS banner."),
        ("Log in as medical@lasu.test.", "Sees only Health & Emergency reports."),
        ("Log in as counsel@lasu.test.", "Sees Harassment & GBV reports; reporter always shown as Anonymous."),
        ("As any unit, check the Assign officer list.", "Only officers of that unit (and the admin) are listed."),
    ]),
    ("10. Administrator", "Log in as admin@lasu.test.", [
        ("Open Reports.", "Sees reports from ALL units, including SOS and GBV."),
        ("Open Users & roles; search and filter by role.", "List of all accounts with role, status and report count."),
        ("Create a staff account (e.g. a new Security Officer).", "Account created; you can log in with it and see the Security dashboard."),
        ("Edit a user: change role.", "On next page load they see the new unit's reports."),
        ("Edit a user: set to Disabled, then try to log in as them.", "Login refused."),
        ("Edit a user: set a new password.", "They can log in with the new password only."),
        ("Try to remove your own admin role or disable yourself.", "Blocked with an error (you can't lock yourself out)."),
    ]),
    ("11. Phone, layout & accessibility", None, [
        ("Open every public page on a phone (or a narrow browser window).", "No sideways scrolling; text readable without zooming."),
        ("Tap all buttons and links on a phone.", "Easy to tap (no tiny targets); nothing overlaps."),
        ("Use the site in bright daylight.", "Text has strong contrast; red is used only for SOS."),
        ("Navigate a form with the keyboard (Tab / Enter).", "Every field can be reached and submitted; focus outline is visible."),
    ]),
    ("12. Deployment checks", None, [
        ("Leave the site idle for 30+ minutes, then open it.", "Loads immediately (Railway does not sleep)."),
        ("Upload a report photo, ask for a redeploy (git push), then open the report again.", "Photo is still there (stored on the Railway volume)."),
        ("Check the address bar on every page.", "Always https:// with a padlock."),
        ("Open /up.", "Shows the health check page (used by Railway to know the app is running)."),
    ]),
]

BEFORE = [
    "Change every demo password (Users & roles) or disable demo accounts.",
    "Replace the 0800000000x phone numbers with official LASU numbers (config/lsw.php).",
    "Check the landmark map coordinates in config/lsw.php against the real campus.",
    "Set SEED_DEMO_REPORTS=false in Railway variables.",
    "Tell Campus Security before testing SOS on the live system.",
]

e = html.escape
rows = []
n = 0
for title, intro, items in SECTIONS:
    rows.append(f"<h2>{e(title)}</h2>")
    if intro:
        rows.append(f"<p class='intro'>{e(intro)}</p>")
    rows.append("<table><thead><tr><th class='n'>#</th><th>What to do</th><th>Expected result</th><th class='c'>Pass</th></tr></thead><tbody>")
    for do, exp in items:
        n += 1
        rows.append(f"<tr><td class='n'>{n}</td><td>{e(do)}</td><td>{e(exp)}</td><td class='c'><span class='box'></span></td></tr>")
    rows.append("</tbody></table>")

acc = "".join(f"<tr><td>{e(r)}</td><td class='mono'>{e(m)}</td><td class='mono'>{e(p)}</td><td>{e(s)}</td></tr>" for r, m, p, s in ACCOUNTS)
before = "".join(f"<li><span class='box'></span>{e(b)}</li>" for b in BEFORE)

css = """
@page { size: A4; margin: 14mm 14mm 16mm; }
* { box-sizing: border-box; }
body { font-family: 'Segoe UI', Calibri, sans-serif; font-size: 9.2pt; color: #0F172A; line-height: 1.35; }
h1 { font-size: 19pt; color: #0E2A50; margin: 0; letter-spacing: -0.02em; }
.sub { color: #475569; margin: 2px 0 10px; }
h2 { font-size: 11.5pt; color: #0E2A50; margin: 14px 0 4px; break-after: avoid; }
.intro { color: #475569; margin: 0 0 4px; font-style: italic; }
table { width: 100%; border-collapse: collapse; margin-bottom: 4px; }
th { background: #0E2A50; color: #fff; text-align: left; font-weight: 600; padding: 4px 6px; font-size: 8.5pt; }
td { border-bottom: 1px solid #E2E8F0; padding: 4px 6px; vertical-align: top; }
tr { break-inside: avoid; }
.n { width: 26px; color: #475569; text-align: right; }
.c { width: 38px; text-align: center; }
.box { display: inline-block; width: 11px; height: 11px; border: 1.3px solid #0E2A50; border-radius: 2px; vertical-align: -1px; margin-right: 6px; }
.c .box { margin: 0; }
.mono { font-family: Consolas, 'JetBrains Mono', monospace; font-size: 8.5pt; }
.info { display: flex; gap: 14px; margin: 8px 0 4px; }
.panel { border: 1px solid #E2E8F0; border-radius: 6px; padding: 8px 10px; flex: 1; }
.panel h3 { margin: 0 0 4px; font-size: 10pt; color: #0E2A50; }
ul.before { list-style: none; padding: 0; margin: 0; }
ul.before li { margin: 3px 0; }
.sign { margin-top: 16px; display: flex; gap: 24px; color: #475569; }
.sign div { flex: 1; border-top: 1px solid #94A3B8; padding-top: 4px; }
"""

doc = f"""<!doctype html><html><head><meta charset='utf-8'><title>LASU Safety Watch - Test Checklist</title><style>{css}</style></head><body>
<h1>LASU Safety Watch: Test Checklist</h1>
<p class='sub'>Every feature of the system, with what to do and what should happen. Tick <b>Pass</b> when the result matches. {n} checks in total.</p>
<div class='info'>
  <div class='panel'><h3>Live site</h3><span class='mono'>{e(URL)}</span><br><span style='color:#475569'>Local copy: http://127.0.0.1:8000 (php artisan serve)</span></div>
  <div class='panel'><h3>Tester / date</h3>Name: ______________________<br>Date: ______________  Device: ______________</div>
</div>
<h2>Test accounts (demo passwords)</h2>
<table><thead><tr><th>Role</th><th>Email</th><th>Password</th><th>Sees</th></tr></thead><tbody>{acc}</tbody></table>
{''.join(rows)}
<h2>Before going live</h2>
<ul class='before'>{before}</ul>
<div class='sign'><div>Tested by</div><div>Signature</div><div>Date</div></div>
</body></html>"""

out = HERE / "LASU_Safety_Watch_Test_Checklist.html"
out.write_text(doc, encoding="utf-8")
print(out, n, "checks")
