---
name: vietnamese-encoding-guard
description: Preserve properly accented Vietnamese and enforce encoding-safe text handling across text-based files. Use when Codex reads, edits, generates, rewrites, overwrites, or exports textual content that contains Vietnamese or may contain Vietnamese, especially in repositories, documents, templates, source files, configs, CSV/JSON/Markdown/text assets, or any workflow where encoding integrity matters. Detect mojibake, ambiguous encodings, and character corruption before writing; verify critical Vietnamese strings remain intact; prefer UTF-8 unless a verified alternative is explicitly required; and block writes when encoding safety cannot be confirmed.
---

# Vietnamese Encoding Guard

Protect Vietnamese text from corruption during any text-based task.

## Core Rules

- Preserve all Vietnamese diacritics exactly.
- Prefer UTF-8 unless a different encoding is explicitly verified.
- Treat uncertain encoding as unsafe.
- Refuse to write when mojibake, decoding ambiguity, or re-encoding risk is present.
- Never replace accented Vietnamese with unaccented ASCII.
- Never "fix" corrupted text by guessing unless the source encoding and intended text are both verified.

## Operating Procedure

### 1. Inspect Before Editing

- Check whether the task touches text that already contains Vietnamese or is likely to contain Vietnamese.
- Examine the target file for signs of encoding damage before proposing or applying edits.
- Determine whether the file can be read and written safely with the current toolchain.
- Preserve the file's existing newline style and surrounding text conventions unless the task requires otherwise.

### 2. Look For Corruption Signals

Treat any of the following as a warning sign:

- Common mojibake patterns such as `Tiáº¿ng Viá»‡t`, `Ã`, `á»`, `â€œ`, `â€`, `Ð`, `?` replacing accented letters, or mixed good/bad Vietnamese in the same file.
- Vietnamese text that appears partially decoded, double-decoded, or inconsistently rendered.
- Files whose encoding cannot be identified with confidence.
- Tool output that shows replacement characters or lossy conversion behavior.
- A workflow that would reopen or rewrite the file through an encoding-unsafe step.

### 3. Establish Write Safety

Only treat a file as safe to write when all of the following are true:

- The current text can be read without corruption.
- The target encoding is known.
- The edited content preserves critical Vietnamese strings exactly.
- The write path will not transcode the file through an unsafe intermediate format.
- There is no evidence of mojibake, ambiguous decoding, or character loss.

If any of those conditions fail, do not write the file.

### 4. Protect Critical Strings

Before any file write:

- Identify the Vietnamese strings most important to preserve.
- Re-check those strings immediately before saving.
- Confirm that the exact accented text remains intact after the planned edit.
- Prefer checking real in-file strings over reconstructed approximations.

Examples of critical strings include:

- User-facing labels and messages
- Product names, headings, and titles
- Legal or policy text
- Seed data, fixtures, or localization entries
- Comments or documentation that the task directly modifies

### 5. Block Unsafe Writes

Stop the write operation immediately if:

- The file already contains mojibake or other corruption.
- The encoding is ambiguous.
- The intended Vietnamese text cannot be verified confidently.
- The editing method risks re-encoding through an unsafe codec.
- A generated export step may degrade Vietnamese characters.

Do not "try anyway." Report the problem instead.

## Reporting Format For Blocked Writes

When blocking a write, report:

- The affected file
- The affected string or region
- The observed corruption pattern
- The most likely cause
- The safest remediation path
- Whether the original intended Vietnamese can or cannot be recovered confidently

Keep the report concrete and operational.

Example structure:

- `File:` `path/to/file`
- `Affected text:` `Tiáº¿ng Viá»‡t`
- `Observed issue:` likely UTF-8 text decoded as Windows-1252/Latin-1, producing mojibake
- `Risk:` writing this file may permanently preserve or spread corrupted Vietnamese
- `Safe remediation:` recover from a known-good UTF-8 source or verify original encoding before rewriting

## Preferred Remediation Path

When encoding risk is detected:

1. Stop the write.
2. Identify whether a known-good source exists.
3. Recover the correct Vietnamese from a verified source if available.
4. Verify the correct encoding before attempting any rewrite.
5. Rewrite only after confirming that critical Vietnamese strings survive round-trip checks.

If no verified source exists, say so plainly and avoid guessing.

## Editing Guidance

- Use tools and workflows that preserve Unicode reliably.
- Favor direct, encoding-safe edits over copy-paste chains through unknown terminals, shells, or editors.
- Be especially careful with CSV, JSON, SQL dumps, Markdown, PHP, JS, TS, HTML, template files, and localization resources.
- Preserve existing Vietnamese exactly when making partial edits.
- If generating new Vietnamese content, validate the final saved form before completing the task.

## Output Discipline

- Call out encoding risk early.
- State clearly when a file is safe to edit and why.
- State clearly when a file is not safe to edit and why.
- Prefer a blocked write over a potentially corrupt write.

## Success Condition

Consider the task successful only when Vietnamese text remains fully accented, readable, and encoding-safe from input through final write, with no mojibake, no silent degradation, and no unverified transcoding.
