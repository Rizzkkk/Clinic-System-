-- 009: Group multiple tests under one laboratory order.
-- A lab order (laboratory_results) can now hold many tests, each with its own result value,
-- reference range, and Normal/Abnormal flag. Tests move into a child table; the parent keeps the
-- shared fields (patientId, testDate, orderedBy, remarks). The parent's per-test columns
-- (testType, results, referenceRange, abnormalFlag) are left in place (unused for new orders) so
-- this migration is non-destructive and reversible.

CREATE TABLE IF NOT EXISTS laboratory_result_items (
  id             INT UNSIGNED NOT NULL AUTO_INCREMENT,
  resultId       INT UNSIGNED NOT NULL,
  testType       VARCHAR(100) NOT NULL,
  results        TEXT,
  referenceRange VARCHAR(100),
  abnormalFlag   VARCHAR(10) DEFAULT 'N',
  created_at     TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  INDEX laboratory_result_item_idx (resultId),
  CONSTRAINT laboratory_result_item_fk FOREIGN KEY (resultId)
      REFERENCES laboratory_results(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Backfill: promote each existing single-test order into one item so every read path uses the
-- child table uniformly. Idempotent - skips orders that already have items.
INSERT INTO laboratory_result_items (resultId, testType, results, referenceRange, abnormalFlag)
SELECT lr.id,
       COALESCE(NULLIF(lr.testType, ''), 'Laboratory test'),
       lr.results,
       lr.referenceRange,
       COALESCE(NULLIF(lr.abnormalFlag, ''), 'N')
FROM laboratory_results lr
WHERE NOT EXISTS (SELECT 1 FROM laboratory_result_items i WHERE i.resultId = lr.id);
