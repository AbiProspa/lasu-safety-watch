"""Builds the 2-page project explanation.
   python docs/build_doc.py   ->  docs/LASU_Safety_Watch_Explained.docx (+ .html used to make the PDF)
"""
import base64
import html
from pathlib import Path

from docx import Document
from docx.enum.table import WD_TABLE_ALIGNMENT
from docx.enum.text import WD_ALIGN_PARAGRAPH
from docx.oxml import OxmlElement
from docx.oxml.ns import qn
from docx.shared import Cm, Pt, RGBColor

HERE = Path(__file__).parent
NAME = "LASU_Safety_Watch_Explained"
GREEN = RGBColor(0x0E, 0x2A, 0x50)  # brand navy

TITLE = "LASU Safety Watch"
SUBTITLE = ("Design and Implementation of a Web-Based Crowdsourced Security and Incident Reporting "
            "System for Lagos State University (LASU)")

# ---------------------------------------------------------------- content
# ("h", text) heading · ("p", text, lead) paragraph · ("b", text, lead) bullet · ("t", rows, widths) table · ("img", [(file, caption)])
C = [
    ("h", "1. What the system does"),
    ("p", "LASU Safety Watch lets students, staff and visitors report security threats and other campus "
          "problems from a phone or computer in about a minute. Each report has a description, a severity, a "
          "campus location with a map pin, and an optional photo. Users can report anonymously. Every report "
          "goes automatically to the LASU unit responsible for it (Campus Security, Works & Services, Medical "
          "Centre or Counselling). That unit verifies the report, assigns an officer, posts updates and marks it "
          "resolved. The reporter follows progress with a tracking code. Management gets a live dashboard with "
          "charts and hotspots.", None),
    ("p", "Today, incidents at LASU are reported by walking to a security post, by phone or on WhatsApp "
          "groups. That leads to slow responses, lost records, no follow-up, and fear of reporting sensitive cases "
          "like cultism and harassment. This system puts every report in one secure place. Each report can be "
          "traced, the reporter's identity is protected, and responses can be measured.", "Problem solved: "),

    ("h", "2. Users and what they can do"),
    ("t", [["User", "What they can do"],
           ["Anyone (no login)", "Submit a report or press SOS, view the live map, track a report with its code, call emergency contacts"],
           ["Student / Staff (account)", "Everything above, plus a \"My reports\" page listing all their reports"],
           ["Security / Works / Medical / Counsellor", "See only their unit's reports; verify, assign, update, resolve; internal notes; CSV export"],
           ["Administrator", "All reports and statistics, plus create staff accounts, change roles, disable users, reset passwords"]],
     [4.6, 12.8]),

    ("h", "3. Main features"),
    ("b", " 6 report types (Security, Harassment & GBV, Hostel, Facility, Environment & Flooding, Health & "
          "Emergency), each with sub-types, a severity level (Low to Critical), date and time, a LASU landmark plus a map "
          "pin (\"use my location\" or tap the map), and photo upload.", "Smart report form:"),
    ("b", " a big red button with a 5-second countdown (which can be cancelled). It sends the user's GPS location to Campus "
          "Security without a login. Security staff see a flashing banner with a sound alert within 15 seconds.",
     "SOS panic button:"),
    ("b", " the user can tick \"report anonymously\". Harassment & GBV reports are always anonymous and never "
          "appear on the public map.", "Anonymous and confidential reporting:"),
    ("b", " each report gets a code such as LSW-7K3P9Q and a public timeline (Received → Verified → In "
          "progress → Resolved).", "Tracking code:"),
    ("b", " an OpenStreetMap map of LASU Ojo campus with coloured pins by type, filters by type and date, and "
          "verified reports only.", "Live incident map:"),
    ("b", " KPIs (total, pending, active, resolved this month, average resolution time), a 14-day trend chart, "
          "category and status charts, top hotspot locations, and a \"needs attention\" list ordered by severity.",
     "Staff dashboard:"),
    ("b", " search and filters (status, category, severity, dates), change status and severity, assign an officer, "
          "add public or internal notes with a full audit trail, directions to the location, and CSV export for Excel.",
     "Case management:"),
    ("b", " one-tap call buttons for LASU and Lagos emergency lines (112, 767), plus safety tips.",
     "Safety Center:"),
    ("b", " role-based access (each unit sees only its own reports), hashed passwords, CSRF protection, "
          "rate-limiting on login, reports and SOS (anti-spam), and validated uploads.", "Security:"),
    ("img", [("home.png", "Figure 1: Home page"), ("map.png", "Figure 2: Live incident map of LASU")]),

    ("h", "4. How it works"),
    ("p", "The system is a Laravel MVC web application. Routes send each request to a Controller. The "
          "Controller uses Models (User, Category, Report, ReportUpdate) to read and write the MySQL database, then "
          "returns a Blade view styled with Tailwind CSS. Maps use Leaflet with OpenStreetMap, charts use "
          "Chart.js, and interactive parts (SOS countdown, map picker, live alerts) use Alpine.js.", None),
    ("p", "The user submits a report → it is validated and saved as Pending, with a tracking code → it "
          "appears on the responsible unit's dashboard → an officer verifies and assigns it (Verified / In "
          "progress) → each action is logged in report_updates → it is marked Resolved. The public map, tracking "
          "page and statistics update automatically.", "Report life-cycle: "),
    ("t", [["Database table", "Purpose"],
           ["users", "Accounts with role (admin, security, works, medical, counsellor, student)"],
           ["categories", "Report types, the unit that handles each, colour, sub-types, confidential flag"],
           ["reports", "Each incident: code, type, severity, status, location, lat/lng, photo, reporter, assigned officer"],
           ["report_updates", "Timeline of every status change, assignment and note (public or internal)"]],
     [3.6, 13.8]),

    ("h", "5. Technologies used"),
    ("p", "PHP 8.3 and Laravel 12 (backend) · MySQL (database; SQLite for local testing) · Blade, Tailwind "
          "CSS 4 and Alpine.js (interface) · Leaflet and OpenStreetMap (maps) · Chart.js (charts) · Vite (asset build) "
          "· Docker, Render and TiDB Cloud MySQL (free deployment). Automated tests: 8 feature tests (php artisan test) covering "
          "reporting, anonymity, SOS, role permissions and the update workflow.", None),

    ("h", "6. Running and deploying"),
    ("p", " composer install → npm install → npm run build → php artisan migrate:fresh --seed → "
          "php artisan serve, then open http://127.0.0.1:8000. Admin login: admin@lasu.test / Admin@1234.",
     "On your PC:"),
    ("p", " the code is on GitHub; Render (free) builds the Dockerfile and runs the site at https://lasu-safety-watch.onrender.com, and TiDB Cloud (free) "
          "provides the MySQL-compatible database over an encrypted connection. On every start the app runs the migrations and "
          "creates the categories and staff accounts automatically (steps in DEPLOY-FREE.md).", "Online (free):"),
    ("p", " change all demo passwords, put the real LASU phone numbers and landmark coordinates in "
          "config/lsw.php, and set SEED_DEMO_REPORTS=false.", "Before going live:"),
]

# ---------------------------------------------------------------- Word
doc = Document()
sec = doc.sections[0]
sec.page_height, sec.page_width = Cm(29.7), Cm(21.0)
sec.top_margin = sec.bottom_margin = Cm(1.4)
sec.left_margin = sec.right_margin = Cm(1.8)
st = doc.styles["Normal"]
st.font.name = "Calibri"
st.font.size = Pt(9.5)
st.paragraph_format.space_after = Pt(3)


def shade(cell, fill):
    tcPr = cell._tc.get_or_add_tcPr()
    shd = OxmlElement("w:shd")
    shd.set(qn("w:val"), "clear"); shd.set(qn("w:color"), "auto"); shd.set(qn("w:fill"), fill)
    tcPr.append(shd)


p = doc.add_paragraph(); p.alignment = WD_ALIGN_PARAGRAPH.CENTER
r = p.add_run(TITLE); r.bold = True; r.font.size = Pt(18); r.font.color.rgb = GREEN
p = doc.add_paragraph(); p.alignment = WD_ALIGN_PARAGRAPH.CENTER
r = p.add_run(SUBTITLE); r.italic = True; r.font.size = Pt(10.5)

for item in C:
    kind = item[0]
    if kind == "h":
        p = doc.add_paragraph(); p.paragraph_format.space_before = Pt(6); p.paragraph_format.space_after = Pt(2)
        r = p.add_run(item[1]); r.bold = True; r.font.size = Pt(12); r.font.color.rgb = GREEN
    elif kind in ("p", "b"):
        p = doc.add_paragraph(style="List Bullet" if kind == "b" else None)
        if kind == "b": p.paragraph_format.space_after = Pt(1)
        if item[2]: p.add_run(item[2]).bold = True
        p.add_run(item[1])
    elif kind == "t":
        rows, widths = item[1], item[2]
        t = doc.add_table(rows=len(rows), cols=len(rows[0])); t.style = "Table Grid"; t.alignment = WD_TABLE_ALIGNMENT.CENTER
        for i, row in enumerate(rows):
            for j, val in enumerate(row):
                c = t.cell(i, j); c.width = Cm(widths[j]); c.text = ""
                run = c.paragraphs[0].add_run(val); run.font.size = Pt(8.5)
                c.paragraphs[0].paragraph_format.space_after = Pt(0)
                if i == 0:
                    run.bold = True; run.font.color.rgb = RGBColor(255, 255, 255); shade(c, "0E2A50")
    elif kind == "img":
        imgs = [(HERE / "screenshots" / f, cap) for f, cap in item[1] if (HERE / "screenshots" / f).exists()]
        if imgs:
            t = doc.add_table(rows=2, cols=len(imgs)); t.alignment = WD_TABLE_ALIGNMENT.CENTER
            for j, (f, cap) in enumerate(imgs):
                t.cell(0, j).paragraphs[0].add_run().add_picture(str(f), width=Cm(8.2))
                cp = t.cell(1, j).paragraphs[0]; cp.alignment = WD_ALIGN_PARAGRAPH.CENTER
                rr = cp.add_run(cap); rr.italic = True; rr.font.size = Pt(8)

docx_path = HERE / f"{NAME}.docx"
doc.save(docx_path)

# ---------------------------------------------------------------- HTML (for the PDF)
e = html.escape
parts = [f"<h1>{e(TITLE)}</h1><p class='sub'>{e(SUBTITLE)}</p>"]
bullets = []


def flush():
    if bullets:
        parts.append("<ul>" + "".join(bullets) + "</ul>"); bullets.clear()


for item in C:
    kind = item[0]
    if kind != "b": flush()
    if kind == "h":
        parts.append(f"<h2>{e(item[1])}</h2>")
    elif kind == "p":
        parts.append(f"<p>{'<b>'+e(item[2])+'</b>' if item[2] else ''}{e(item[1])}</p>")
    elif kind == "b":
        bullets.append(f"<li><b>{e(item[2])}</b>{e(item[1])}</li>")
    elif kind == "t":
        rows = item[1]
        head = "".join(f"<th>{e(c)}</th>" for c in rows[0])
        body = "".join("<tr>" + "".join(f"<td>{e(c)}</td>" for c in r) + "</tr>" for r in rows[1:])
        parts.append(f"<table><tr>{head}</tr>{body}</table>")
    elif kind == "img":
        figs = ""
        for f, cap in item[1]:
            fp = HERE / "screenshots" / f
            if fp.exists():
                b64 = base64.b64encode(fp.read_bytes()).decode()
                figs += f"<figure><img src='data:image/png;base64,{b64}'><figcaption>{e(cap)}</figcaption></figure>"
        parts.append(f"<div class='figs'>{figs}</div>")
flush()

css = """
@page { size: A4; margin: 14mm 18mm; }
body { font-family: Calibri, 'Segoe UI', sans-serif; font-size: 9.5pt; color: #1f2a24; line-height: 1.32; }
h1 { text-align: center; color: #0E2A50; font-size: 18pt; margin: 0; }
.sub { text-align: center; font-style: italic; font-size: 10.5pt; margin: 2px 0 6px; }
h2 { color: #0E2A50; font-size: 12pt; margin: 9px 0 3px; }
p { margin: 0 0 4px; } ul { margin: 0 0 4px; padding-left: 18px; } li { margin-bottom: 2px; }
table { width: 100%; border-collapse: collapse; margin: 3px 0 5px; font-size: 8.5pt; }
th { background: #0E2A50; color: #fff; text-align: left; } th, td { border: 1px solid #b8c4d6; padding: 3px 5px; vertical-align: top; }
.figs { display: flex; gap: 10px; margin: 6px 0; } figure { margin: 0; flex: 1; text-align: center; }
figure img { width: 100%; border: 1px solid #ccd6d0; } figcaption { font-style: italic; font-size: 8pt; }
"""
html_path = HERE / f"{NAME}.html"
html_path.write_text(f"<!doctype html><html><head><meta charset='utf-8'><title>{TITLE}</title><style>{css}</style></head>"
                     f"<body>{''.join(parts)}</body></html>", encoding="utf-8")
print(docx_path)
print(html_path)
